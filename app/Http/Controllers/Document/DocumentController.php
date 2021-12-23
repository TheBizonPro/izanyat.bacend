<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request\DocumentApiUploadRequest;
use App\Services\Documents\DocumentArchiveService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use DB;
use Auth;
use Illuminate\Support\Facades\Log;
use Storage;
use Carbon\Carbon;

use Yajra\DataTables\Facades\DataTables;

use App\Models\Document;
use App\Models\Project;

use App\Jobs\SignMe\MassSignDocuments;
use App\Models\User;
use App\Services\Telegram\TelegramAdminBotClient;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use Response;

class DocumentController extends Controller
{

    public function documents(Request $request)
    {
        $type = [];

        if ($request->get('types'))
            $type = explode(',', $request->types);

        if ($type || count($type) > 0) {
            $types = Document::types();
            Arr::add($types, 'attachments', 'Приложения');
            $isCorrectType = Arr::has($types, $type);
            if (!$isCorrectType) {
                return response()->json([
                    'title' => 'Ошибка',
                    'message' => 'Указанного типа нет!'
                ], 400);
            }
        }

        $me = Auth::user();

        if ($me->company_id != null) {
            $documents = Document::whereCompanyId($me->company_id)->when($type, function ($query, $type) {
                return $query->whereIn('type', $type);
            })->get();
        } else {
            $documents = Document::where('user_id', $me->id)->when($type, function ($query, $type) {
                return $query->whereIn('type', $type);
            })->get();
        }
        //todo переделать
        $documentsFiltered = $documents->map(function (Document $document) {
            return [
                'id' => $document->id,
                'company_sign_requested' => $document->company_sign_requested,
                'contractor_inn' => $document->user->inn,
                'contractor_name' => $document->user->name,
                'document_date' => $document->date,
                'document_name' => $document->name,
                'document_id' => $document->id,
                'document_link' => $document->link,
                'document_type' => $document->type,
                'is_signed_by_company' => $document->is_signed_by_company,
                'is_signed_by_user' => $document->is_signed_by_user,
                'project_name' => $document->project->name,
                'user_sign_requested' => $document->user_sign_requested,
                'type' => $document->type
            ];
        });

        return response()->json(['documents' => $documentsFiltered]);
    }

    public function types(Request $request)
    {
        $me = Auth::user();

        $documentTypes = array_keys(Document::types());

        $countEachTypeOfDocument = [];
        foreach ($documentTypes as $type) {
            $count = 0;
            if ($me->company_id != null) {
                if ($me->isCompanyAdmin()) {
                    $count = Document::whereCompanyId($me->company_id)->where('type', $type)
                        ->when($request->project_id, function ($query, $projectID) {
                            return $query->whereHas('project', function ($query) use ($projectID) {
                                return $query->where('id', $projectID);
                            });
                        })->get()->count();
                } else {
                    $projectIds = $me->projects()->pluck('project_id')->toArray();
                    $count = Document::whereIn('project_id', $projectIds)->where('type', $type)
                        ->when($request->project_id, function ($query, $projectID) {
                            return $query->whereHas('project', function ($query) use ($projectID) {
                                return $query->where('id', $projectID);
                            });
                        })->get()->count();
                }
            } else {
                $count = Document::where('user_id', $me->id)->where('type', $type)
                    ->when($request->project_id, function ($query, $projectID) {
                        return $query->whereHas('project', function ($query) use ($projectID) {
                            return $query->where('id', $projectID);
                        });
                    })->get()->count();
            }
            $countEachTypeOfDocument[] = ['type' => $type, 'count' => $count];
        }
        $allCount = 0;
        if ($me->company_id != null) {
            if ($me->isCompanyAdmin()) {
                $allCount = Document::whereCompanyId($me->company_id)->when($request->project_id, function ($query, $projectID) {
                    return $query->whereHas('project', function ($query) use ($projectID) {
                        return $query->where('id', $projectID);
                    });
                })->get()->count();
            } else {
                $projectIds = $me->projects()->pluck('project_id')->toArray();
                $allCount = Document::whereIn('project_id', $projectIds)->when($request->project_id, function ($query, $projectID) {
                    return $query->whereHas('project', function ($query) use ($projectID) {
                        return $query->where('id', $projectID);
                    });
                })->get()->count();
            }
        } else {
            $allCount = Document::where('user_id', $me->id)->when($request->project_id, function ($query, $projectID) {
                return $query->whereHas('project', function ($query) use ($projectID) {
                    return $query->where('id', $projectID);
                });
            })->get()->count();
        }
        $countEachTypeOfDocument[] = ['type' => 'all', 'count' => $allCount];
        return response()->json($countEachTypeOfDocument);
    }



    public function datatable(Request $request)
    {
        $me = Auth::user();

        if ($request->project_id) {
            $project = Project::where('id', '=', $request->project_id)->first();
            if ($project == null) {
                return response()
                    ->json([
                        'title' => 'Ошибка',
                        'message' => 'Проект не существует'
                    ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
            }
        }


        $documents = Document::query();
        if ($request->project_id) {
            $documents = $documents->where('documents.project_id', '=', $project->id);//->orderByDesc('documents.created_at');
        }
        $documents = $documents->with('user');

        $documents= $documents->orderByDesc('documents.created_at');
        //$documents = $documents->with('order');
        $documents = $documents->leftJoin('users', 'users.id', '=', 'documents.user_id');
        $documents = $documents->leftJoin('projects', 'projects.id', '=', 'documents.project_id');
        //$documents = $documents->leftJoin('orders', 'orders.id', '=', 'documents.order_id');

        if (!$me->company_id) {
            $documents = $documents->where('users.id', $me->id);
        } else {
            if ($me->isCompanyAdmin()) {
                $documents = $documents->where('documents.company_id', $me->company_id);
            } else {
                $projectIds = $me->projects()->pluck('project_id')->toArray();
                $documents = $documents->whereIn('documents.project_id', $projectIds);
            }
        }
        /**
         * Фильтрация данных
         */
        if ($request->filter) {

            if (Arr::exists($request->filter, 'document_type')) {
                if ($request->filter['document_type'] != 'all') {
                    $documents = $documents
                        ->where('documents.type', '=', $request->filter['document_type']);
                }
            }

            if (Arr::exists($request->filter, 'order_id')) {
                $documents = $documents
                    ->where('documents.order_id', '=', $request->filter['order_id']);
            }

            if (Arr::exists($request->filter, 'contractor')) {
                $contractor = $request->filter['contractor'];
                $documents = $documents
                    ->where(function ($query) use ($contractor) {
                        $query
                            ->whereRaw('users.lastname like ?', ["{$contractor}%"])
                            ->orWhereRaw('users.inn like ?', ["{$contractor}%"]);
                    });
            }

            if (Arr::exists($request->filter, 'date_from')) {
                $date_from = Carbon::createFromFormat('d.m.Y', $request->filter['date_from'])
                    ->format('Y-m-d');

                $documents = $documents
                    ->where('documents.date', '>=', $date_from);
            }

            if (Arr::exists($request->filter, 'date_till')) {
                $date_till = Carbon::createFromFormat('d.m.Y', $request->filter['date_till'])
                    ->format('Y-m-d');

                $documents = $documents
                    ->where('documents.date', '<=', $date_till);
            }
            if (Arr::exists($request->filter, 'project_id')) {
                $documents = $documents
                    ->where('documents.project_id', $request->filter['project_id']);
            }

            if (Arr::exists($request->filter, 'status')) {
                $status = $request->filter['status'];
                $documents = match ($status) {
                    'not_requested' => $documents
                        ->where('company_sign_requested', 0)
                        ->orWhere('user_sign_requested', 0),
                    'requested' => $documents
                        ->where(function ($query) {
                            $query
                                ->whereNull('company_sig')
                                ->whereNull('user_sig')
                                ->where(function ($query) {
                                    $query->where('company_sign_requested', 1)->orWhere('user_sign_requested', 1);
                                });
                        }),
                    'signed' => $documents
                        ->whereNotNull('company_sig')
                        ->orWhereNotNull('user_sig')
                };
            }
        }

        $select = [];
        $select[] = DB::raw('documents.id as id');
       // $select[] = DB::raw('documents.created_at as created_at');
        $select[] = DB::raw('documents.type as type');
        //$select[] = DB::raw('documents.user_id as user_id');
        $select[] = DB::raw('documents.task_id as task_id');
        $select[] = DB::raw('documents.payout_id as payout_id');
        $select[] = DB::raw('documents.number as number');
        $select[] = DB::raw('documents.date as date');
        $select[] = DB::raw('documents.file as file');
        $select[] = DB::raw('projects.id as project_id');
        $select[] = DB::raw('projects.name as project_name');
        //$select[]= DB::raw('orders.name as order_name');

        $select[] = DB::raw('documents.company_sign_requested as company_sign_requested');
        $select[] = DB::raw('documents.user_sign_requested as user_sign_requested');
        $select[] = DB::raw('documents.company_sig as company_sig');
        $select[] = DB::raw('documents.user_sig as user_sig');

        $select[] = DB::raw('users.id as user_id');
        $select[] = DB::raw('users.lastname as user_lastname');
        $select[] = DB::raw('users.inn as user_inn');

        $documents = $documents->select($select);

        $dataTable = DataTables::eloquent($documents);



        $dataTable = $dataTable->addColumn('project_name', function (Document $document) {
            return $document->project_name;
        });
        $dataTable = $dataTable->filterColumn('project_name', function ($query, $keyword) {
            $query->whereRaw('projects.name = ?', ["{$keyword}"]);
        });
        $dataTable = $dataTable->orderColumn('project_name', function ($query, $order) {
            $query->orderBy('projects.name', $order);
        });


        /*        $dataTable = $dataTable->addColumn('order_name', function(Document $document){
            return $document->order_name;
        });
        $dataTable = $dataTable->filterColumn('order_name', function($query, $keyword) {
            $query->whereRaw('orders.name = ?', ["{$keyword}"]);
        });
        $dataTable = $dataTable->orderColumn('order_name', function ($query, $order) {
            $query->orderBy('orders.name', $order);
        });*/


        $dataTable = $dataTable->addColumn('document_id', function (Document $document) {
            return $document->id;
        });
        $dataTable = $dataTable->filterColumn('document_id', function ($query, $keyword) {
            $query->whereRaw('documents.id = ?', ["{$keyword}"]);
        });
        $dataTable = $dataTable->orderColumn('document_id', function ($query, $order) {
            $query->orderBy('documents.id', $order);
        });


        $dataTable = $dataTable->addColumn('contractor_name', function (Document $document) {
            return $document->user->name;
        });
        $dataTable = $dataTable->filterColumn('contractor_name', function ($query, $keyword) {
            $query->whereRaw('users.lastname like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('contractor_name', function ($query, $order) {
            $query->orderBy('users.lastname', $order);
        });


        $dataTable = $dataTable->addColumn('contractor_inn', function (Document $document) {
            return $document->user->inn;
        });
        $dataTable = $dataTable->filterColumn('contractor_inn', function ($query, $keyword) {
            $query->whereRaw('users.inn like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('contractor_inn', function ($query, $order) {
            $query->orderBy('users.inn', $order);
        });


        $dataTable = $dataTable->addColumn('document_type', function (Document $document) {
            return $document->type;
        });
        $dataTable = $dataTable->filterColumn('document_type', function ($query, $keyword) {
            $query->whereRaw('documents.type like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('document_type', function ($query, $order) {
            $query->orderBy('documents.type', $order);
        });


        $dataTable = $dataTable->addColumn('document_date', function (Document $document) {
            return $document->date;
        });
        $dataTable = $dataTable->filterColumn('document_date', function ($query, $keyword) {
            // 2do проверка формата даты. Если дд.мм.гггг или гггг-мм-дд - то ищем
            $query->whereRaw('documents.date like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('document_date', function ($query, $order) {
            $query->orderBy('documents.date', $order);
        });


        $dataTable = $dataTable->addColumn('document_name', function (Document $document) {
            return $document->name;
        });
        $dataTable = $dataTable->orderColumn('document_name', function ($query, $order) {
            $query->orderBy('documents.type', $order);
        });



        $dataTable = $dataTable->addColumn('document_link', function (Document $document) {
            return $document->link;
        });


        $dataTable = $dataTable->addColumn('company_sign_requested', function (Document $document) {
            return $document->company_sign_requested;
        });

        $dataTable = $dataTable->addColumn('user_sign_requested', function (Document $document) {
            return $document->user_sign_requested;
        });


        $dataTable = $dataTable->addColumn('is_signed_by_company', function (Document $document) {
            return $document->is_signed_by_company;
        });

        $dataTable = $dataTable->addColumn('is_signed_by_user', function (Document $document) {
            return $document->is_signed_by_user;
        });

        $dataTable = $dataTable->orderColumn('sign_status', function ($query, $order) {
            $query->orderBy(DB::raw('CONCAT(documents.company_sign_requested, "-", documents.user_sign_requested)'), $order);
        });


        $only = [
            'document_id',
            'project_name',
            'contractor_name',
            'contractor_inn',
            'document_name',
            'document_type',
            'document_date',
            'document_link',
            'order_name',
            'company_sign_requested',
            'user_sign_requested',
            'is_signed_by_company',
            'is_signed_by_user'
        ];

        $dataTable = $dataTable->only($only);

        $dataTable = $dataTable->smart(true);
        return $dataTable->make(true);
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('file') == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не передан файл с реестром!'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $validMimeTypes = [
            'application/pdf',
        ];

        $mimeType = mime_content_type($request->file->getPathname());
        if (in_array($mimeType, $validMimeTypes) == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Плохой тип файла (' . $mimeType . ')'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }
        $path = Storage::disk('public')->putFile('temp', $request->file('file'));
        $url = Storage::disk('public')->url($path);

        Log::channel('debug')->debug("FILE IS $url");

        return response()
            ->json([
                'title' => 'Файл загружен',
                'message' => 'Файл успешно загружен',
                'path' => $path,
                'preview' => Storage::disk('public')->url($path)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function show(Request $request)
    {
        $document = Document::where('id', '=', $request->document_id)->firstOrFail();

        return Response::make(Storage::disk('cloud')->get($document->file), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->name . '"'
        ]);
    }

    public function get(Request $request)
    {
        $me = Auth::user();

        $document = Document::where('id', '=', $request->document_id)->firstOrFail();

        $zip = new \ZipArchive();
        $file = storage_path("app/temp/doc_" . $document->id . ".zip");
        if (!is_dir(storage_path("app/temp/"))) {
            mkdir(storage_path("app/temp/"), 0777);
        }
        if ($zip->open($file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception('Cannot create a zip file');
        }

        if (Storage::disk('cloud')->exists($document->file)) {

            $extension = pathinfo($document->file)['extension'];
            $name = $document->name . '.' . $extension;

            $zip->addFromString($document->id . '/' . $name, Storage::disk('cloud')->get($document->file));
        } else {
            echo "Документ не найден в облаке (" . $document->file . ")";
            exit;
        }

        if ($document->company_sig != null) {
            if (Storage::disk('cloud')->exists($document->company_sig)) {
                $zip->addFromString($document->id . '/company_sig.pks7', Storage::disk('cloud')->get($document->company_sig));
            }
        }

        if ($document->user_sig != null) {
            if (Storage::disk('cloud')->exists($document->user_sig)) {
                $zip->addFromString($document->id . '/user_sig.pks7', Storage::disk('cloud')->get($document->user_sig));
            }
        }

        $zip->close();
        $frontUrl = config('app.front_url');
        header("Access-Control-Allow-Origin: $frontUrl");
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($file));
        header('Content-Name: ' . $document->id . ".zip");
        header('Content-Disposition: attachment; filename="doc_' . $document->id . '.zip"');
        readfile($file);
        unlink($file);
    }

    /**
     * Возвращает архив всех документов пользователя
     *
     * @param Request $request
     * @throws Exception
     */
    public function getAll(Request $request)
    {
        $me = Auth::user();

        if ($me->is_client) {
            if ($me->isCompanyAdmin()) {
                $documentsIds = Document::whereCompanyId($me->company_id)->pluck('id')->toArray();
            } else {
                $availableProjectIds = $me->projects()->pluck('project_id')->toArray();
                $documentsIds = Document::whereIn('project_id', $availableProjectIds)->pluck('id')->toArray();
            }
        } else {
            $documentsIds = Document::whereUserId($me->id)->pluck('id')->toArray();
        }

        $frontUrl = config('app.front_url');
        header("Access-Control-Allow-Origin: $frontUrl");
        header('Access-Control-Allow-Credentials: true');
        $documentService = new DocumentArchiveService();
        $documentService->streamArchive($documentsIds);
    }
    /**
     * Возвращает архив документов, полученных из облака по id
     *
     * @param Request $request
     * @throws Exception
     */
    public function getMany(Request $request)
    {
        $docIds = $request->ids;
        if (!$docIds || !is_array($docIds)) {
            throw new Exception('Wrong format');
        }


        $frontUrl = config('app.front_url');
        header("Access-Control-Allow-Origin: $frontUrl");
        header('Access-Control-Allow-Credentials: true');

        $documentService = new DocumentArchiveService();
        $documentService->streamArchive($docIds);
    }

    public function createDocument(DocumentApiUploadRequest $request)
    {
        if ($request->hasFile('document') == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не передан файл с реестром!'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $validMimeTypes = [
            'application/pdf',
        ];

        $mimeType = mime_content_type($request->document->getPathname());
        if (in_array($mimeType, $validMimeTypes) == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Плохой тип файла (' . $mimeType . ')'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $user = User::where('inn', $request->inn)->first();
        if ($user == null || !$user->is_selfemployed) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Самозанятого нет в системе'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $filePath = Storage::disk('public')->putFile('temp', $request->file('document'));

        $project = Project::where('id', '=', $request->project_id)->first();

        if ($project == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Проект не существует'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $currentContract = Document::whereProjectId($project->id)->whereCompanyId($project->company_id)->whereUserId($user->id)->whereType('contract')->first();

        if ($currentContract !== null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'С исполнителем можно иметь только 1 договор'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $fileRaw = Storage::disk('public')->get($filePath);
        $path = 'projects/' . $request->project_id . '/' . $request->document_type . '/' . uniqid() . '.pdf';
        Storage::disk('cloud')->put($path, $fileRaw);
        Storage::disk('public')->delete($request->document_path);


        $document = new Document;
        $document->type = 'contract';
        $document->project_id = $request->project_id;
        $document->user_id = $user->id;
        $document->number = $request->number;
        $document->date = Carbon::createFromFormat('d.m.Y', $request->date)->format('Y-m-d');
        $document->file = $path;
        $document->hash = md5($fileRaw);
        $document->company_sig = null;
        $document->user_sig = null;
        $document->company_id = $project->company_id;

        $document->save();
        $document->refresh();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Документ успешно создан'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function create(Request $request)
    {
        $me = Auth::user();

        $project = Project::where('id', '=', $request->project_id)->first();
        if ($project == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Проект не существует'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $currentContract = Document::whereProjectId($request->project_id)->whereCompanyId($project->company_id)->whereUserId($request->document_user_id)->whereType('contract')->first();

        if ($request->document_type === 'contract' && $currentContract !== null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'С исполнителем можно иметь только 1 договор'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $fileRaw = Storage::disk('public')->get($request->document_path);
        $path = 'projects/' . $request->project_id . '/' . $request->document_type . '/' . uniqid() . '.pdf';
        Storage::disk('cloud')->put($path, $fileRaw);
        Storage::disk('public')->delete($request->document_path);


        $document = new Document;
        $document->type = $request->document_type;
        $document->project_id = $request->project_id;
        $document->user_id = $request->document_user_id;
        $document->number = $request->document_number;
        $document->date = Carbon::createFromFormat('d.m.Y', $request->document_date)->format('Y-m-d');
        $document->file = $path;
        $document->hash = md5($fileRaw);
        $document->company_sig = null;
        $document->user_sig = null;
        $document->company_id = $project->company_id;

        $document->save();
        $document->refresh();

        // $signStatus = null;
        // try {
        //    $signStatus = SignDocument::dispatchNow($document);
        // } catch (\Throwable $e) {
        //    $signStatus = $e->getMessage();
        // }


        /**
         * Если это договор добавляем ключ документа в ProjectUser
         */
        // if ($document->type == 'contract') {
        /*            $projectUser = ProjectUser::query()
                ->where('project_id', '=', $document->project_id)
                ->where('user_id', '=', $document->user_id)
                ->first();
            if ($projectUser != null) {
                $projectUser->document_id = $document->id;
                $projectUser->save();
            }*/
        //  }

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Документ успешно создан'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    /**
     * Получение документов не отправленных на подпись
     */
    public function unrequestedSignsCount(Request $request)
    {
        $me = Auth::user();
        if ($me->company_id == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не установлена компания клиента'
                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $unrequestedSignsCount = Document::leftJoin('projects', 'projects.id', '=', 'documents.project_id')
            ->where('projects.company_id', '=', $me->company_id)
            ->when($request->project_id, function ($query, $id) {
                return $query->where('projects.id', $id);
            })
            ->where(function ($query) {
                $query
                    ->where('documents.company_sign_requested', '=', false)
                    ->orWhere('documents.user_sign_requested', '=', false);
            })
            ->count();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Данные получены',
                'unrequested_signs_count' => $unrequestedSignsCount
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    /**
     * Запросить подписание 1 или более документов
     */
    public function requestSignDocumentsInScope(Request $request)
    {
        $me = Auth::user();
        if ($me->company_id == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не установлена компания клиента'
                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }
        $documentIDs = $request->get('documents');
        if (!$documentIDs || count($documentIDs) == 0) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не указаны документы для подписания'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $documentsToSign = Document::where('company_id', $me->company_id)->whereIn('id', $documentIDs)->get(); //whereNull(['company_sign_requested', 'user_sign_requested'])->get();

        try {
            $result = MassSignDocuments::dispatchNow($documentsToSign);
        } catch (\Throwable $th) {
            TelegramAdminBotClient::sendAdminNotification('ERRROR SINGME');
            return response()->json([
                'title' => 'Ошибка',
                'message' => $th->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'result' => $result,
                'message' => 'Отправлено пакетов: ' . count($result['success']) . ', не отправлено пакетов:' . count($result['errors']),
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    /**
     * Запросить подписание всех документов
     */
    public function requestSignDocuments(Request $request)
    {
        $me = Auth::user();
        if ($me->company_id == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не установлена компания клиента'
                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $unrequestedSignDocuments = Document::leftJoin('projects', 'projects.id', '=', 'documents.project_id')
            ->where('projects.company_id', '=', $me->company_id)
            ->where(function ($query) {
                $query
                    ->where('documents.company_sign_requested', '=', false)
                    ->orWhere('documents.user_sign_requested', '=', false);
            })
            ->when($request->project_id, function ($query, $project_id) {
                return $query->where('projects.id', $project_id);
            })
            ->select([
                DB::raw('documents.*'),
                DB::raw('projects.id as projects_project_id'),
                DB::raw('projects.company_id')
            ])
            ->get();

        try {
            $result = MassSignDocuments::dispatchNow($unrequestedSignDocuments);
        } catch (\Throwable $e) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Ошибка отправки документов на подпись: ' . $e->getMessage(),
                ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'result' => $result,
                'message' => 'Отправлено пакетов: ' . count($result['success']) . ', не отправлено пакетов:' . count($result['errors']),
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    /**
     * Список проектов клиента
     */
    public function clientProjectsDatatable(Request $request)
    {
        $me = Auth::user();
        if ($me->is_client == false) {
            return abort(403);
        }

        $projects = $request->user()->getProjects()
            ->withCount(['tasks']);

        if ($request->filter) {
            if (Arr::exists($request->filter, 'name')) {
                $name = $request->filter['name'];
                $projects = $projects->where('name', 'LIKE', "%$name%");
            }
        }

        $dataTable = DataTables::eloquent($projects);

        $dataTable = $dataTable->addColumn('tasks_count', function (Project $project) {
            return $project->tasks_count;
        });


        //$dataTable = $dataTable->addColumn('orders_count', function(Project $project) {
        //	return $project->orders_count;
        //});
        //$dataTable = $dataTable->orderColumn('orders_count', function ($query, $order) {
        //	$query->orderBy('orders_count', $order);
        //});


        //$dataTable = $dataTable->addColumn('users_count', function(Project $project) {
        //	return $project->users_count;
        //});
        //$dataTable = $dataTable->orderColumn('users_count', function ($query, $order) {
        //	$query->orderBy('users_count', $order);
        //});


        $dataTable = $dataTable->addColumn('created_date', function (Project $project) {
            return Carbon::parse($project->created_at)->format('d.m.Y');
        });

        $dataTable = $dataTable->orderColumn('created_date', function ($query, $order) {
            $query->orderBy('created_at', $order);
        });

        $dataTable = $dataTable->smart(true);
        return $dataTable->make(true);
    }
}

<?php

namespace App\Jobs\SignMe;

use App\Services\LogsService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use DB;
use Psr\Http\Client\ClientExceptionInterface;
use Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Models\Document;

use PackFactory\SignMe\DTO\FileDTO;
use PackFactory\SignMe\Exceptions\SignMeResponseException;
use PackFactory\SignMe\SignMe;

class MassSignDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $errors;
    private $success;
    private Collection $documents;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Collection $documents)
    {
        $this->errors = [];
        $this->success = [];
        $this->documents = $documents;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SignMe $signMe, LogsService $logsService)
    {
//        foreach ($this->documents as $document) {
//            $this->requestCompanySign($document);
//            $this->requestUserSign($document);
//        }
        // TODO логирование
                $documentsToSignByUser = [];
        $documentsToSignByCompany = [];

        foreach($this->documents as $document) {
            if (! $document->user_sign_requested) {
                $documentsToSignByUser[$document->user->id][]= $document;
                $logsService->signmeLog('Документ отправлен на подпись', $document->user->id, $document->toArray());
            }

            if (! $document->company_sign_requested) {
                $documentsToSignByCompany[$document->project->company->id][]= $document;
                $logsService->signmeLog('Документ отправлен на подпись', $document->project->company->signer_user_id, $document->toArray());
            }
        }

        /**
         * Подписание со стороны компании
         */
        foreach ($documentsToSignByCompany as $company_id => $documents) {
            $filesArray = $this->getFilesArray($documents);

            $company = Company::find($company_id);
            $requestParams = [];
            $requestParams['company_inn'] = $company->forSignMe('cinn');
            try {
                $response = $signMe->signFiles($filesArray, $requestParams);
                $sign_requested = 1;
            } catch(ClientExceptionInterface $e){
//                $this->errors[$requestParams['user_ph']]= $e->getMessage();//. " " . $e->getFile() . " " . $e->getLine();
                $sign_requested = 0;
            }

            foreach ($documents as $document) {
                // dump('set document id ' . $document->id . ' company_sign_requested to '.  $sign_requested);
                $document->company_sign_requested = $sign_requested;
                $document->save();
            }
        }



        /**
         * Подписание со стороны пользователя
         */
        foreach ($documentsToSignByUser as $user_id => $documents) {
            // dump('- для юзера ' . $user_id);
            $filesArray = $this->getFilesArray($documents);

            $user = User::find($user_id);
            $requestParams = [];
            $requestParams['user_ph'] = $user->forSignMe('phone');

            try {
                $response = $signMe->signFiles($filesArray, $requestParams);
                $this->success[$requestParams['user_ph']]= $response;
                $sign_requested = 1;
            } catch(\Throwable $e){
                $this->errors[$requestParams['user_ph']]= $e->getMessage(); // . " " . $e->getFile() . " " . $e->getLine();
                $sign_requested = 0;
            }

            foreach ($documents as $document) {
                //dump('set  document id ' . $document->id . ' user_sign_requested to '.  $sign_requested);
                $document->user_sign_requested = $sign_requested;
                $document->save();
            }
        }


        return(['success' => $this->success, 'errors' => $this->errors]);

    }

    protected function getFilesArray(array $documents): array
    {
        $files = [];
        foreach ($documents as $document) {
            // dump('-- документ ' . $document->id);

            $fileName = $document->name;
            $contentRaw = Storage::disk('cloud')->get($document->file);
            $contentRawBase64 = base64_encode($contentRaw);
            $hash = md5($contentRaw);

            $attributes = [
                'filet'   => $contentRawBase64,
                'fname'   => $fileName,
                // 'user_ph' => $document->user->forSignMe('phone'),
                'md5'     => $hash,
            ];
            $files[]= FileDTO::createFromArray($attributes);
        }

        return $files;
    }


    protected function requestCompanySignDocumentPack(Document $document)
    {

    }

    protected function requestUserSignDocumentPack(Document $document)
    {

    }


}

<?php

namespace App\Http\Controllers\Offer;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use App\Services\LogsService;
use App\Services\TasksService;
use Illuminate\Http\Request;
use App\Models\Offer;
use Auth;

use App\Http\Resources\OfferResource;
use App\Models\Notification;

class OfferController extends Controller
{
    protected TasksService $tasksService;
    protected LogsService $logsService;

    /**
     * @param TasksService $tasksService
     * @param LogsService $logsService
     */
    public function __construct(TasksService $tasksService, LogsService $logsService)
    {
        $this->tasksService = $tasksService;
        $this->logsService = $logsService;
    }


    /**
     * Принятие оффера
     */
    public function accept(Request $request)
    {

        $me = Auth::user();
        $offer = Offer::where('id', '=', $request->offer_id)->first();

        if ($offer == null) {
            return abort(404);
        }

        if (! $me->hasAccessToProject($offer->task->project_id))
            return \Response::json(['error' => 'Не разрешено'], 403);


        $this->logsService->userLog('Принято предложение от самозанятого ' . $offer->user_id, $me->id, $offer->toArray());

        SendNotificationJob::dispatch(
            $offer->user,
            'Вас выбрали исполнителем!',
            "Заказчик выбрал вас для исполнения задачи <a href='/contractor/tasks/my/{$offer->task->id}'> {$offer->task->name} </a> , теперь она отображается у вас в разделе «Мои задачи»",
            $offer->task->project->company->name
        );

        $this->logsService->userLog('Отправлено уведомление самозанятому о принятии отклика работодателем' . $offer->user_id, $me->id, $offer->toArray());




        $offer->accepted = true;
        $offer->save();

        $this->tasksService->assign($offer->task, $offer->user);
        $offer->refresh();

        //        $offer->task->user_id = $offer->user_id;
        //        $offer->task->status = 'work';
        //        $offer->task->save();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Исполнитель выбран',
                'offer' => new OfferResource($offer)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }
}

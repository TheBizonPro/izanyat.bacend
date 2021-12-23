<?php

namespace App\Jobs\Documents;

use App\Models\Task;
use App\Services\Documents\DocumentsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Payout;
use App\Models\Document;
use App\Services\Telegram\TelegramAdminBotClient;
use \Mpdf\Mpdf;
use View;
use Storage;

class CreateAct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Task $task;

    /**
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }


    /**
     * Execute the job.
     *
     * @return false
     * @throws \Exception
     */
    public function handle(DocumentsService $documentsService)
    {
        TelegramAdminBotClient::sendAdminNotification($this->task->sum ?? 'сумма не проставлена');
        $act = $documentsService->createAct($this->task);
        $this->task->act_id = $act->id;
        $this->task->save();
        return $act;

        //        $payout = $this->payout;
        //        $document = new Document;
        //        $document->type = 'act';
        //        $document->project_id = $payout->project_id;
        ////        $document->order_id = $payout->order_id;
        //        $document->user_id = $payout->user_id;
        //        $document->payout_id = $payout->id;
        //        $document->number = $payout->id;
        //        $document->date = date('Y-m-d');
        //        $document->company_sig = null;
        //        $document->user_sig = null;
        //
        //        $mpdf = new \Mpdf\Mpdf([
        //            'mode' => 'utf-8',
        //            'format' => 'A4',
        //            'default_font_size' => 10,
        //            'default_font' => 'Arial',
        //            'margin_left' => 10,
        //            'margin_right' => 10,
        //            'margin_top' => 10,
        //            'margin_bottom' => 10,
        //            'orientation' => 'P',
        //        ]);
        //
        //        $html = View::make('documents.act', ['payout' => $payout])->render();
        //        $mpdf->WriteHTML($html);
        //        $pdf_raw = $mpdf->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
        //
        //        $path = 'projects/' . $payout->order->project_id . '/orders/' . $payout->order_id . '/act_' . $payout->id . '.pdf';
        //        Storage::disk('cloud')->put($path, $pdf_raw);
        //
        //        $document->file = $path;
        //        $document->hash = md5($pdf_raw);
        //        $document->save();
        //
        //        return $document->id;
    }
}

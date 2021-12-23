<?php

namespace App\Jobs\Documents;

use App\Services\Documents\DocumentsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;
use App\Models\Document;
use \Mpdf\Mpdf;
use View;
use Storage;

class CreateWorkOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Task $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(DocumentsService $documentsService)
    {
        $order = $documentsService->createOrder($this->task);
        $this->task->order_id = $order->id;
        $this->task->save();
        return $order;
//        $task = $this->task;
//        $document = new Document;
//        $document->type = 'work_order';
//        $document->project_id = $task->project_id;
//        $document->user_id = $task->user_id;
//        $document->task_id = $task->id;
//        $document->number = $task->id;
//        $document->date = date('Y-m-d');
//        $document->company_sig = null;
//        $document->user_sig = null;
//
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
//        $html = View::make('documents.work_order', ['task' => $task])->render();
//        $mpdf->WriteHTML($html);
//        $pdf_raw = $mpdf->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
//
//        $path = 'projects/' . $task->project_id . '/work_order_' . $task->id . '.pdf';
//        Storage::disk('cloud')->put($path, $pdf_raw);
//
//        $document->file = $path;
//        $document->hash = md5($pdf_raw);
//        $document->save();
//
//        return $document->id;
    }
}

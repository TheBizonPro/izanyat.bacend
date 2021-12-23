<?php

namespace App\Jobs\Documents;

use App\Services\LogsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Payout;
use App\Models\Document;

use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;

/**
 * Создает чек
 *
 * Class CreateReceipt
 * @package App\Jobs\Documents
 */
class CreateReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payout;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payout $payout)
    {
        $this->payout = $payout;
    }

    /**
     * Execute the job.
     *
     * @return mixed
     * @throws MpdfException
     */
    public function handle(LogsService $logsService)
    {
        $logsService->userLog('Начато создание оффлайн чека', $this->payout->user_id, $this->payout->toArray());
        $payout = $this->payout;

        $document = new Document;
        $document->type = 'reciept';
        $document->project_id = $payout->project_id;
        $document->user_id = $payout->user_id;
        $document->payout_id = $payout->id;
        $document->number = $payout->id;
        $document->date = date('Y-m-d');
        $document->company_sig = null;
        $document->user_sig = null;

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font_size' => 10,
            'default_font' => 'Arial',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'orientation' => 'P',
        ]);

        $html = view('documents.receipt', ['payout' => $payout])->render();
        $mpdf->WriteHTML($html);
        $pdf = $mpdf->Output(null, Destination::STRING_RETURN);

        $path = 'projects/' . $payout->project_id . '/receipt_' . $payout->id . '.pdf';
        Storage::disk('cloud')->put($path, $pdf);

        $document->file = $path;
        $document->hash = md5($pdf);
        $document->save();

        $logsService->userLog('Оффлайн чек создан', $this->payout->user_id, $document->toArray());

        return $document->id;
    }
}

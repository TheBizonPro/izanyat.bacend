<?php

namespace App\Http\Controllers\Receipt;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use chillerlan\QRCode\QRCode;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

/**
 * Контроллер чеков
 *
 * Class ReceiptController
 * @package App\Http\Controllers\Receipt
 */
class ReceiptController extends Controller
{
    /**
     * Переадресовывает на чек в НПД
     *
     * @param $inn
     * @param $approvedReceiptUuid
     * @return Application|Factory|View
     * @throws Exception
     */
    public function print($inn, $approvedReceiptUuid)
    {
        $payout = Payout::getForOfflineReceipt($inn, $approvedReceiptUuid);
        if (!$payout || !$payout->receipt_url) {
            throw new Exception('Чек не найден');
        }

        $qrCode = (new QRCode())->render($payout->getReceiptUrl(true));

        return view('documents.receipt', ['payout' => $payout, 'qr_data' => $qrCode]);
    }
}

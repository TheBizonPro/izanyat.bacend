<?php

namespace App\Console\Commands\FNS;

use App\Services\Fns\FNSService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

use League\CLImate\CLImate;
use App\Models\Payout;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

class AnnulateReceipt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fns:annulate_receipt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Аннулирование чека';

    private $terminal;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->terminal = new CLImate;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(FNSService $fnsService)
    {
        $this->terminal->clear();
        $this->terminal->yellow('Пожалуйста введите ID выплаты для аннулирования чека!');
        $input = $this->terminal->input('ID выплаты: ');
        $payout_id = $input->prompt();

        $payout = Payout::where('id', '=', $payout_id)->first();

        if ($payout == null) {
            $this->terminal->red('Выплата не найдена!');
            exit;
        }

        if ($payout->receipt_id == null) {
            $this->terminal->red('В выплате нет чека!');
            exit;
        }


        $this->terminal->white('Ссылка на чек ' .  $payout->receipt_url);
        $input = $this->terminal->input('Вы точно хотите аннулировать чек ' . $payout->receipt_id . ': [да / нет]');
        $r = $input->prompt();


        if ($r != 'да' && $r != 'yes' && $r != 'y') {
            exit;
        }


        $this->terminal->yellow('Причина аннулирования чека:');
        $this->terminal->white('1) Возврат средств');
        $this->terminal->white('2) Чек сформирован ошибочно');
        $input = $this->terminal->input('Выберите причину (1/2):');
        $reason = $input->prompt();

        if ($reason == 1) {
            $ReasonCode = 'REFUND';
        } else if($reason == 2) {
            $ReasonCode = 'REGISTRATION_MISTAKE';
        } else {
            $this->terminal->red('Ошибка! Неверный код причины!');
            exit;
        }

        $this->terminal->yellow('Запрос аннулирования чека...');

//        $masterToken = env('FNS_MASTER_TOKEN');
//        $userToken = "1";
//        $ktirUrl = config('npd.ktir_url');
//        $FnsSmzClient = new FnsSmzClient($masterToken, $userToken, $ktirUrl);
//        $FnsSmzClient->setStoringTempToken(true);
//        $FnsSmzClient->setCacheDir(storage_path('app/cache'));
//        $FnsSmzClient->authIfNecessary(config('npd.auth_url'));
//        $FnsSmzApi = new FnsSmzApi($FnsSmzClient);

        $Inn = $payout->user->inn;
        $ReceiptId = $payout->receipt_id;


        $answer = $fnsService->annulateReceipt($Inn,$ReceiptId,$ReasonCode);

        dump($answer);

        $payout->status = 'canceled';
        $payout->save();

        $this->terminal->white('Ссылка на чек ' .  $payout->receipt_url);
        $this->terminal->yellow('Работа скрипта завершена');


    }
}

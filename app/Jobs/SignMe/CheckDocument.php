<?php

namespace App\Jobs\SignMe;

use App\Services\LogsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Models\Document;

use PackFactory\SignMe\DTO\FileDTO;
use PackFactory\SignMe\Exceptions\SignMeResponseException;
use PackFactory\SignMe\SignMe;

class CheckDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $signMe;
    private Document $document;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->signMe = new SignMe(config('signme.key'), config('signme.sandbox'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LogsService $logsService)
    {
       $signResponse = $this->signMe->checkFile($this->document->hash);
       dump($signResponse);
       $logsService->signmeLog('Получили ответ по проверке документы', $this->document->user_id, [
           'signme_resp' => json_encode($signResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
       ]);
       if ($signResponse->count > 0) {

            foreach ($signResponse->signatures as $signatureDTO) {

                if (isset($signatureDTO->company)) {
                    $logsService->signmeLog('Получили ответ по проверке документов компании', $this->document->company_id, [
                        'signme_resp' => json_encode($signatureDTO->company, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    ]);

                    if ($this->document->project->company->inn == $signatureDTO->company->inn) {
                        $path = $this->document->file . '.client.sig';
                        $this->document->company_sig = $path;
                        Storage::disk('cloud')->put($path, $signatureDTO->pkcs64);
                    }

                } else {
                    $logsService->signmeLog('Получили ответ по проверке документов пользователя', $this->document->user_id, [
                        'signme_resp' => json_encode($signatureDTO->company, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    ]);

                    if ($this->document->user->forSignMe('phone') == $signatureDTO->person->phone_number) {
                        $path = $this->document->file . '.contractor.sig';
                        $this->document->user_sig = $path;
                        Storage::disk('cloud')->put($path, $signatureDTO->pkcs64);
                    }

                }
            }
       }

       $this->document->save();


    }
}

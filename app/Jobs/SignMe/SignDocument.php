<?php

namespace App\Jobs\SignMe;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Models\Document;

use PackFactory\SignMe\DTO\FileDTO;
use PackFactory\SignMe\Exceptions\SignMeResponseException;
use PackFactory\SignMe\SignMe;

class SignDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $signMe;
    private $document;
    private $userForceRequest;
    private $companyForceRequest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($document, $userForceRequest = false, $companyForceRequest = false)
    {
        $this->document = $document;
        $this->userForceRequest = $userForceRequest;
        $this->companyForceRequest = $companyForceRequest;
        $this->signMe = new SignMe(config('signme.key'), config('signme.sandbox'), null, config('signme.logging.start'), storage_path('logs/signme_' . uniqid() . '.log'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $statuses = [];
        $document = $this->document;
        $fileName = $document->name;
        $contentRaw = Storage::disk('cloud')->get($document->file);
        $contentRawBase64 = base64_encode($contentRaw);
        $hash = md5($contentRaw);


        /**
         * Запрос на подпись клиенту
         */
        $company_sign_status = [];
        if ($document->company_sign_requested == false OR $this->companyForceRequest == true) {
            $attributes = [
                'filet'       => $contentRawBase64,
                'fname'       => $fileName,
                'company_inn' => $document->project->company->forSignMe('cinn'),
                'user_ph'     => $document->project->company->signerUser->forSignMe('phone'),
                'md5'         => $hash,
            ];


            $FileDTO = FileDTO::createFromArray($attributes);

            $response = $this->signMe->signFile($FileDTO);

            // Ищем в ответе от SignMe ошибку
            $searchForError = mb_strpos($response, 'error');
            if ($searchForError === false) {
                $document->company_sign_requested = true;
                $company_sign_status['sign_requested'] = true;
                $company_sign_status['response'] = $response;
            } else {
                $company_sign_status['sign_requested'] = false;
                $company_sign_status['response'] = $response;
            }
        } else {
            $company_sign_status['sign_requested'] = true;
        }


        /**
         * Запрос на подпись исполнителю
         */
        $user_sign_status = [];
        if ($document->user_sign_requested == false OR $this->userForceRequest == true) {
            $attributes = [
                'filet'   => $contentRawBase64,
                'fname'   => $fileName,
                'user_ph' => $document->user->forSignMe('phone'),
                'md5'     => $hash,
            ];
            $FileDTO = FileDTO::createFromArray($attributes);
            $response = $this->signMe->signFile($FileDTO);

            // Ищем в ответе от SignMe ошибку
            $searchForError = mb_strpos($response, 'error');
            if ($searchForError === false) {
                $document->user_sign_requested = true;
                $user_sign_status['sign_requested'] = true;
                $user_sign_status['response'] = $response;
            } else {
                $user_sign_status['sign_requested'] = false;
                $user_sign_status['response'] = $response;
            }
        } else {
            $user_sign_status['sign_requested'] = true;
        }

        $document->save();

        return [
            'user' => $user_sign_status,
            'company' => $company_sign_status
        ];
    }
}
            
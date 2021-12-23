<?php

namespace App\Services\Documents;

use App\Jobs\SendNotificationJob;
use App\Models\Company;
use App\Models\Document;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class DocumentsService
{
    /**
     * @throws \Exception
     */
    public function createAgreement(Task $task): Document
    {
        $document = $this->makeDocument($task, 'agreement', true);

        $pdf = $this->createPDF('documents.agreement', [
            'task' => $task,
            'company' => $task->project->company,
            'user' => $task->user,
            'agreement' => $document
        ]);

        $path = $this->generateCloudPath($task, 'agreement');

        return $this->saveDocument($document, $pdf, $path);
    }

    /**
     * @throws \Exception
     */
    public function createAct(Task $task): Document
    {
        $agreement = Document::whereType('contract')->whereCompanyId($task->project->company_id)->whereProjectId($task->project->id)->whereUserId($task->user_id)->first();

        if (!$agreement) {
            //Send notification to all company users
            $task->project->users->each(function ($user) use ($task) {
                SendNotificationJob::dispatch(
                    $user,
                    'Ошибка',
                    "Договор по проекту {$task->project->name} с самозанятым {$task->user->name} не найден, невозможно составить акт"
                );
            });
            SendNotificationJob::dispatchSync(
                $task->company()->signerUser,
                'Ошибка',
                "Договор по проекту {$task->project->name} с самозанятым {$task->user->name} не найден, невозможно составить акт"
            );

            throw new \Exception('Договор не найден, невозможно составить акт');
        }

        $document = $this->makeDocument($task, 'act');

        $pdf = $this->createPDF('documents.act', [
            'task' => $task,
            'agreement' => $agreement
        ]);

        $path = $this->generateCloudPath($task, 'act');

        return $this->saveDocument($document, $pdf, $path);
    }

    /**
     * @throws \Exception
     */
    public function createOrder(Task $task): Document
    {
        $document = $this->makeDocument($task, 'work_order');

        $pdf = $this->createPDF('documents.work_order', [
            'task' => $task
        ]);

        $path = $this->generateCloudPath($task, 'order');

        return $this->saveDocument($document, $pdf, $path);
    }

    protected function makeDocument(
        Task $task,
        string $type,
        bool $nullProjectId = false
    ): Document {
        $document = new Document;
        $document->type = $type;
        $document->project_id = $nullProjectId ? null : $task->project_id;
        $document->user_id = $task->user_id;
        $document->payout_id = null;
        $document->number = $task->id;
        $document->date = date('Y-m-d');
        $document->company_id = $task->project->company->id;
        $document->company_sig = null;
        $document->user_sig = null;

        return $document;
    }

    public function saveDocument(Document $document, $pdf, $path): Document
    {
        Storage::disk('cloud')->put($path, $pdf);

        $document->file = $path;
        $document->hash = md5($pdf);
        $document->save();

        return $document;
    }

    /**
     * @throws \Exception
     */
    public function generateCloudPath(Task $task, string $documentType): string
    {
        switch ($documentType) {
            case 'agreement':
                return 'agreements/user_' . $task->user->id . '/agreement_' . $task->id . '.pdf';
            case 'act':
                return 'projects/' . $task->project_id . '/acts/act_' . $task->id . '.pdf';
            case 'order':
                return 'projects/' . $task->project_id . '/orders/order_' . $task->id . '.pdf';
        }

        throw new \Exception('Unsupported document type');
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    protected function createPDF(string $viewName, array $params)
    {
        $mpdf = new \Mpdf\Mpdf([
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

        $html = View::make($viewName, $params)->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
    }
}

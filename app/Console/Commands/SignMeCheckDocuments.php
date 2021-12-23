<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Jobs\SignMe\CheckDocument;

class SignMeCheckDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signme:check_documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка документов';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dump('Проверка подписания документов в SignMe');

        $documents = Document::query()
            ->where(function($q){
                $q->where('user_sign_requested', '=', true)->whereNull('user_sig');
            })
            ->orWhere(function($q){
                $q->where('company_sign_requested', '=', true)->whereNull('company_sig');
            })
            ->get();

        dump('Ожидают подписания: ' . $documents->count());

        foreach ($documents as $document) {
            dump('Проверка документа ID#' . $document->id . ' ' . $document->name);
            try {
                CheckDocument::dispatchNow($document);
            } catch (\Throwable $e) {
                dump($e->getMessage());
            }
        }
    }
}

<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Barracuda\ArchiveStream\Archive as ArchiveStream;
use Log;

/**
 * Сервис для работы с архивами документов
 *
 * Class DocumentArchiveService
 * @package App\Services\Documents
 */
class DocumentArchiveService
{
    /**
     * Передает динамический файл .tar или .zip
     * тип файла подбирается в зависимости от пользовательского агента
     *
     * @param array $docIds - id документов
     * @throws FileNotFoundException
     * @throws Exception
     * @return void
     */
    public function streamArchive(array $docIds)
    {
        $archive = ArchiveStream::instance_by_useragent(rand());

        ob_start();

        foreach ($docIds as $id) {
            $document = Document::find($id);
            if (!$document) {
                continue;
            }

            try {
                $this->addFiles($archive, $document);
            } catch (Throwable $e) {
                throw new Exception('File not found');
            }

            ob_flush();
            flush();
            ob_clean();
        }

        $archive->finish();
    }

    /**
     * Добавляет файлы документов в динамический архив
     *
     * @param ArchiveStream $archive
     * @param Document $document
     * @throws FileNotFoundException
     * @return void
     */
    private function addFiles(ArchiveStream $archive, Document $document)
    {
        $this->addToArchive($archive, $document, $document->file, $document->file);

        if ($document->company_sig) {
            $this->addToArchive($archive, $document, $document->company_sig, $document->company_sig, true);
        }

        if ($document->user_sig) {
            $this->addToArchive($archive, $document, $document->user_sig, $document->user_sig, true);
        }
    }

    /**
     * Добавляет файл из облака в динамический архив
     *
     * @param ArchiveStream $archive
     * @param Document $document
     * @param string $file
     * @throws FileNotFoundException
     * @return void
     */
    private function addToArchive(ArchiveStream $archive, Document $document, string $file, string $filePath, bool $isSign = false)
    {
        $cloudFile = Storage::disk('cloud')->get($file);
        if (!$isSign) {
            $path = $this->getDocFilePath($document, $filePath);
        } else {
            $path = $this->getSignFilePath($document, $filePath);
        }
        $archive->add_file($path, $cloudFile);
    }

    /**
     * Возвращает имя директории
     *
     * @param Document $document
     * @param User $performer
     * @return string
     */
    private function getDirName(Document $document, User $performer): string
    {
        if ($document->type === 'contract') {
            $docType = 'Договор';
        } else {
            $docType = 'Заказ - наряды, акты, чеки';
        }

        $docName = $document->name;

        return 'ИНН_' . $performer->inn . '_' . $performer->getFullNameAttribute() . '/' . $docType . '/' . $docName;
    }

    /**
     * Возвращает имя документа
     *
     * @param Document $document
     * @param User $performer
     * @return string
     */
    private function getDocName(Document $document, User $performer): string
    {
        $types = Document::types();

        return $types[$document->type] . '_'
            . $document->id . '_'
            . $performer->inn . '_'
            . $performer->lastname;
    }

    private function getSignName(Document $document): string
    {
        return 'Подпись';
    }

    /**
     * Возвращает путь к документу
     *
     * @param Document $document
     * @param $cloudFile
     * @return string
     */
    private function getDocFilePath(Document $document, $cloudFile): string
    {
        $performer = $document->user;
        Log::channel('debug')->debug($cloudFile);
        return $this->getDirName($document, $performer) . '/'
            . $this->getDocName($document, $performer)
            . '.' . File::extension($cloudFile);
    }

    private function getSignFilePath(Document $document, $path): string
    {
        $performer = $document->user;
        $pathArray = explode('.', $path);
        $extention = implode('.', array_slice($pathArray, count($pathArray) - 2));
        return $this->getDirName($document, $performer) . '/'
            . $this->getSignName($document)
            . '.' . $extention;
    }
}

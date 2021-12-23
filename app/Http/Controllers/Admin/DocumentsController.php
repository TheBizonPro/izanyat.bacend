<?php

namespace App\Http\Controllers\Admin;

use App\Models\Document;

class DocumentsController
{
    public function signDoc(int $docId)
    {
        Document::findOrFail($docId)->update([
            'user_sig' => 'fake_sig',
            'company_sig' => 'fake_sig',
            'user_sign_requested' => 1,
            'company_sign_requested' => 1,
        ]);

        return [
            'message' => 'Документ успешно подписан'
        ];
    }

    public function index()
    {
        return [
          'documents' => Document::all()
        ];
    }
}

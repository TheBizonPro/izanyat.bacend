<?php

namespace App\Http\Controllers\JobCategory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobCategory;

class JobCategoryController extends Controller
{
    public function list()
    {
        $job_categories = JobCategory::orderBy('sort')->get();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Список категорий загружен',
                'job_categories' => $job_categories
            ], 200, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
    }
}

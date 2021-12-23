<?php

namespace App\Http\Controllers\Region;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
class RegionController extends Controller
{
    public function list()
    {
        $regions = Region::orderBy('id')->get();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Список регионов загружен',
                'regions' => $regions
            ], 200, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
    }
}

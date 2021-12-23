<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Fomvasss\Dadata\Facades\DadataSuggest;
use Illuminate\Support\Arr;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Auth;

class CompanyController extends Controller
{

    public function get(Request $request)
    {
        $company = Company::where('id', '=', $request->company_id)->first();
        if ($company == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Компания не найдена'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        return response()
            ->json([
                'title' => 'Ошибка',
                'message' => 'Компания не найдена',
                'company' => new CompanyResource($company)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }



    public function updateProfile(Request $request)
    {
        $me = Auth::user();
        if ($me->company_id == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не установлена компания клиента'
                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $company = $me->company;
        $company->about = $request->about;
        $company->save();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Профиль компании обновлен'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }



    public function getDataByInn(Request $request)
    {
        $inn = $request->inn;
        try {
            $result = DadataSuggest::partyById($inn);

            if (is_array($result) == false) {
                return response()
                    ->json([
                        'error_code' => 'entity_not_found',
                        'title' => 'Ошибка',
                        'message' => 'Контрагент не найден'
                    ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
            }

            if (Arr::get($result, 'data') == null) {
                // это ликвидированный ИП
                // ебучая дадата просто меняет формат выдачи в этом случае
                $result = Arr::get($result, '0');
            }

            $data['inn']   = Arr::get($result, 'data.inn');
            $data['ogrn']  = Arr::get($result, 'data.ogrn');
            $data['kpp']   = Arr::get($result, 'data.kpp');
            $data['okpo']  = Arr::get($result, 'data.okpo');
            $data['name']  = Arr::get($result, 'value');
            $data['email'] = Arr::get($result, 'data.emails.0');
            $data['phone'] = Arr::get($result, 'data.phones.0');
            $data['address'] = Arr::get($result, 'data.address.value');
            $data['head'] = Arr::get($result, 'data.management.name');

            $data['firstname'] = "";
            $data['fathername'] = "";
            $data['lastname'] = "";

            if (Arr::get($result, 'data.opf.short') == "ИП") {
                $explode = explode(' ', Arr::get($result, 'data.name.full'));
                $data['firstname']  = Arr::get($explode, '1');
                $data['fathername'] = Arr::get($explode, '2');
                $data['lastname']   =  Arr::get($explode, '0');
                $data['head']       = Arr::get($result, 'data.name.full');
            }

            return response()
                ->json([
                    'title' => 'Успешно',
                    'message' => 'Данные организации загружены',
                    'company' => $data
                ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $th) {
            // if (is_array($result) == false) {
            return response()
                ->json([
                    'error_code' => 'entity_not_found',
                    'title' => 'Ошибка',
                    'message' => 'Контрагент не найден'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
            // }
        }
    }
}

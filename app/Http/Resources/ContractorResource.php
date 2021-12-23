<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ContractorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,
            'inn' => $this->inn,
            'name' => $this->name,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'patronymic' => $this->patronymic,
            'sex' => $this->sex,

            'taxpayer_bind_id' => $this->taxpayer_bind_id,
            'taxpayer_registred_as_npd' => $this->taxpayer_registred_as_npd,
            'taxpayer_binded_to_platform' => $this->taxpayer_binded_to_platform,
            'taxpayer_income_limit_not_exceeded' => $this->taxpayer_income_limit_not_exceeded,
            'rating' => $this->rating ?? 0,
            'about' => $this->about ?? 'Нет информации',
            'job_category_id' => $this->job_category_id,
            'job_category_name' => $this->jobCategory->name ?? null,

            'is_identified' => $this->is_identified,

            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'passport_series' => $this->passport_series,
            'passport_number' => $this->passport_number,
            'passport_code' => $this->passport_code,
            'passport_issuer' => $this->passport_issuer,
            'passport_issue_date' => $this->passport_issue_date,
            'snils' => $this->snils,
            'address_region' => $this->address_region,
            'address_city' => $this->address_city,
            'address_street' => $this->address_street,
            'address_house' => $this->address_house,
            'address_building' => $this->address_building,
            'address_flat' => $this->address_flat,

            'is_administrator' => $this->is_administrator,
            'is_client' => $this->is_client,
            'is_selfemployed' => $this->is_selfemployed,
        ];
    }
}

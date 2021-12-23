<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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

			'name' => $this->name,
			'full_name' => $this->full_name,
			'address_region' => $this->address_region,
			'address_city' => $this->address_city,
			'legal_address' => $this->legal_address,
			'fact_address' => $this->fact_address,
			'inn' => $this->inn,
			'ogrn' => $this->ogrn,
			'okpo' => $this->okpo,
			'email' => $this->email,
			'about' => $this->about,
			'phone' => $this->phone,
		];
	}
}

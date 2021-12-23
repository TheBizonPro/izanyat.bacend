<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

use App\Http\Resources\OfferResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ContractorResource;

class TaskResource extends JsonResource
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
            'id'        => $this->id,
            'name'      => $this->name,
            'status'    => $this->status,
            'date_from' => Carbon::parse($this->date_from)->format('d.m.Y'),
            'date_till' => Carbon::parse($this->date_till)->format('d.m.Y'),
            'sum'       => $this->sum,
            'offers_count' => $this->offers()->count(),
            'job_category_id' => $this->job_category_id,
            'job_category_name' => $this->jobCategory->name ?? '',
            'description' => $this->description,
            'address' => $this->address,
            'project_id' => $this->project_id,
            'project_name' => $this->project->name ?? '',
            'is_sum_confirmed' => $this->is_sum_confirmed,
            'company' => new CompanyResource($this->project->company) ?? null,
            'user' => new ContractorResource($this->user) ?? null,
            'confirmed_offer' => new OfferResource($this->offers()->where('accepted', '=', 1)->first()) ?? null,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ContractorResource;
use Carbon\Carbon;
class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'task_id' => $this->task_id,
            'user' => new ContractorResource($this->user),
            'accepted' => $this->accepted,
            'created_datetime' => Carbon::parse($this->created_at)->format('d.m.Y в H:i:s'),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OutgoingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'no_header' => $this->no_header,
            'date_header' => $this->date_header,
            'flow_seq' => $this->flow_seq,
            'doctype_id' => $this->doctype_id,
            'user' => $this->user->name,
            'location' => $this->location,
            'flow_desc' => $this->flow_desc,
            'detail' => $this->outgoingDetail->load('products'),
            // 'detail' => new d_PurchasingResource($this->purchasingDetail),
        ];
    }
}

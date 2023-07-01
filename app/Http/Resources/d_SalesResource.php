<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class d_SalesResource extends JsonResource
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
            'customer' => $this->customer,
            'flow_desc' => $this->flow_desc,
            'is_printed' => $this->is_printed,
            'subtotal' => $this->subtotal,
            'disc_value' => $this->disc_value,
            'disc_percent' => $this->disc_percent,
            'disc_percentvalue' => $this->disc_percentvalue,
            'extra_charge' => $this->extra_charge,
            'dpp' => $this->dpp, // subtotal - disc_value - discpercentvalue + extra_charge
            'vat_type' => $this->vat_type,
            'vat_percent' => $this->vat_percent,
            'vat_value' => $this->vat_value, // vat_percent x dpp
            'grandtotal' => $this->grandtotal, // dpp + vat_value
            'detail' => $this->salesDetail->load('product'),
            'return' => $this->salesReturn->load('product')
        ];
    }
}

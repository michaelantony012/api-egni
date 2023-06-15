<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentLogResource extends JsonResource
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
        // dd($this->purchasingDetail);
        return [
            'id' => $this->id,
            'id_method' => $this->id_method,
            'paymentMethod_name' => $this->paymentMethod->payment_name,
            'ref_no' => $this->ref_no,
            'value' => $this->value,
            'charge_value' => $this->charge_value
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
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
            'payment_name' => $this->payment_name . "::" . $this->account_name . "(" . $this->account_number . ")",
            // 'payment_charge' => $this->payment_charge,
            // 'account_number' => $this->account_number,
            // 'account_name' => $this->account_name,
            'bank' => $this->bank
        ];
    }
}

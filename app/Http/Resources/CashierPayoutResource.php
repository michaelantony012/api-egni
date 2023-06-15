<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CashierPayoutResource extends JsonResource
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
            'id_user' => $this->id_user,
            'user_name' => $this->user->name,
            'cash_in' => $this->cash_in,
            'cash_out' => $this->cash_out,
            'online_payment' => $this->online_payment
        ];
    }
}

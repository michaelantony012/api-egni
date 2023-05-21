<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class opt_ProductResource extends JsonResource
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
            'barcode' => $this->barcode,
            'product_code' => $this->product_code,
            'product_name' => $this->product_name,
            'category' => $this->category,
            'sub_category' => $this->subCategory,
            'product_price' => $this->product_price,

        ];
    }
}

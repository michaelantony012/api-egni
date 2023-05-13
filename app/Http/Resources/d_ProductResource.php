<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class d_ProductResource extends JsonResource
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
            'product_price' => $this->product_price,
            'category' => $this->category,
            'sub_category' => $this->subCategory,
            'primary_stock' => $this->primary_stock,
            'qty_count' => $this->qty_count,
        ];
    }
}

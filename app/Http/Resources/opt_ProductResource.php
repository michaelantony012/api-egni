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
            'product_name' => $this->barcode . '-' . $this->product_code . '::' . $this->product_name,
        ];
    }
}

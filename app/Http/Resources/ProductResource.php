<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        // return [
        //     'id' => $this->id,
        //     'barcode' => $this->barcode,
        //     'product_code' => $this->product_code,
        //     'product_name' => $this->product_name,
        //     'product_price' => $this->product_price,
        //     'category' => $this->category->category_name,
        //     'sub_category' => $this->subCategory->sub_category_name,
        //     'primary_stock' => $this->primary_stock,
        //     'qty_count' => $this->qty_count,
        //     'join_id' => $this->join_id,
        //     'meta' => $this->meta,
        //     'links' => $this->links
        // ];
    }
}

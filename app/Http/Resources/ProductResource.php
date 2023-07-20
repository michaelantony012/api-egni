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
        // dd($this);
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'barcode' => $this->barcode,
            'product_code' => $this->product_code,
            'product_name' => $this->product_name,
            'product_price' => $this->product_price,
            'category_name' => $this->category_name,
            'sub_category_name' => $this->sub_category_name,
            // 'category' => $this->category->category_name,
            // 'sub_category' => $this->subCategory->sub_category_name,
            'primary_stock' => $this->primary_stock,
            'qty_count' => $this->qty_count,
            'join_id' => $this->join_id,
            'qty_stock' => $this->qty_stock,
            'trans_cogs' => $this->trans_cogs
            // 'meta' => $this->meta,
            // 'links' => $this->links
        ];
    }
}

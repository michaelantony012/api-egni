<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocFlowResource extends JsonResource
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
            'doctype_no' => $this->doctype_no,
            'doctype_desc' => $this->doctype_desc,
            'doc_flow' => $this->doc_flow,
            'doc_flow_logic' => $this->doc_flow_logic,

        ];
    }
}

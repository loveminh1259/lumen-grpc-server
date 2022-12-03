<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "product_id" => $this->product_id,
            "quantity" => isset($this->cart_items[0]) ? $this->cart_items[0]->quantity : 0,
            "updated_at" => optional($this->product)->view_updated_time
        ];
    }
}

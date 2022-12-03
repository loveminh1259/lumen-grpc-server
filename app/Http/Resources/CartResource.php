<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'ip_address' => $this->ip_address,
            'ship_to' => $this->ship_to,
            'shipping_zone_id' => $this->shipping_zone_id,
            'shipping_rate_id' => $this->shipping_rate_id,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'shipping_weight' => $this->shipping_weight,
            'packaging_id' => $this->packaging_id,
            'coupon' => $this->coupon,
            'total' => number_format($this->total, 0),
            'shipping' => number_format($this->shipping, 0),
            'packaging' => number_format($this->packaging, 0),
            'handling' => number_format($this->handling, 0),
            'taxrate' => $this->taxrate,
            'taxes' => number_format($this->taxes, 0),
            'discount' => number_format($this->discount, 0),
            'grand_total' => number_format($this->grand_total, 0),
            'shop' => CartShopResource::collection($this->shops),
        ];
    }
}

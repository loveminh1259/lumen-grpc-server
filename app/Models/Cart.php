<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'carts';

    public function inventories() {
        return $this->belongsToMany(Inventory::class, 'cart_items')
            ->withPivot('item_description', 'quantity', 'unit_price', 'attributes')->withTimestamps();
    }

    public function cart_items() {
        return $this->hasMany(CartItem::class);
    }
    public function shops() {
        return $this->hasManyThrough(Shop::class, CartItem::class, 'cart_id', 'id', 'id', 'shop_id')->distinct();
    }
}

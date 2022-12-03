<?php

namespace App\Models;

use App\Scopes\CustomScope;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    use CustomScope;
    public function product_view() {
        return $this->hasOne(ProductView::class, 'product_id', 'id');
    }

    public function getViewUpdatedTimeAttribute() {
        return $this->product_view ? $this->product_view->updated_at : $this->updated_at->timestamp;
    }

    public function shop() {
        return $this->belongsTo(Shop::class);
    }

    public function inventories() {
        return $this->hasMany(Inventory::class, 'product_id');
    }

    //Scope

    public function scopeAvailable($query) {
        return $query->whereHas('shop', function ($q) {
            $q->where('active', 1);
        })->whereHas('inventories', function ($q) {
            $q->where('stock_quantity', '>', 0);
        })->where('active', 1)->whereCompleteContent();
    }

    public function scopeWhereCompleteContent($query) {
        return $query->whereNotNull('description')->whereNotNull('size_info')->whereNotNull('ship_info')->whereNotNull('privacy_policy');
    }
}

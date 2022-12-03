<?php

namespace App\Models;

use App\Scopes\CustomScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model {
    use CustomScope;
    protected $table = 'inventories';

    public function shop() {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Product::class)->withDefault()->withoutGlobalScopes();
    }

    public function cart_items() {
        return $this->hasMany(CartItem::class, 'inventory_id');
    }
    public function attribute_values() {
        return $this->belongsToMany(AttributeValue::class, 'attribute_inventory')->orderBy('color', 'desc')
            ->withPivot('attribute_id')->withTimestamps();
    }

    /**
     * Setters
     */
    public function setMinOrderQuantityAttribute($value) {
        if ($value > 1)  $this->attributes['min_order_quantity'] = $value;
        else $this->attributes['min_order_quantity'] = 1;
    }
    public function setOfferPriceAttribute($value) {
        if ($value >= 0) $this->attributes['offer_price'] = $value;
        else $this->attributes['offer_price'] = null;
    }
    public function setWarehouseIdAttribute($value) {
        if ($value > 0) $this->attributes['warehouse_id'] = $value;
        else $this->attributes['warehouse_id'] = null;
    }
    public function setSupplierIdAttribute($value) {
        if ($value > 0) $this->attributes['supplier_id'] = $value;
        else $this->attributes['supplier_id'] = null;
    }
    public function setAvailableFromAttribute($value) {
        if ($value) $this->attributes['available_from'] = Carbon::createFromFormat('Y-m-d h:i:a', $value);
    }
    public function setOfferStartAttribute($value) {
        if ($value) $this->attributes['offer_start'] = Carbon::createFromFormat('d-m-Y H:i:s', $value);
        else $this->attributes['offer_start'] = null;
    }
    public function setOfferEndAttribute($value) {
        if ($value) $this->attributes['offer_end'] = Carbon::createFromFormat('d-m-Y H:i:s', $value);
        else $this->attributes['offer_end'] = null;
    }
    public function setFreeShippingAttribute($value) {
        $this->attributes['free_shipping'] = (bool) $value;
    }
    public function setKeyFeaturesAttribute($value) {
        if (is_array($value))
            $value = array_filter($value, function ($item) {
                return !empty($item[0]);
            });

        $this->attributes['key_features'] = serialize($value);
    }
    public function setLinkedItemsAttribute($value) {
        $this->attributes['linked_items'] = serialize($value);
    }
    /**
     * Getters
     */

    public function getPackagingListAttribute() {
        if (count($this->packagings)) return $this->packagings->pluck('id')->toArray();
    }
    public function getCodeAttribute() {
        return $this->attributes['code'] == null ? "" : $this->attributes['code'];
    }
    public function getOrderQuantityAttribute() {
        return $this->orders()->where('order_status_id', Order::STATUS_CONFIRMED)->pluck('order_items.quantity')->sum();
    }
    public function getPackageInfoAttribute() {
        return $this->product->package_info;
    }
    public function getManufacturerAttribute() {
        return $this->product->manufacturer;
    }
    public function getItemDescriptionAttribute() {
        $values = $this->attributeValues->pluck('value')->toArray();
        return implode(' - ', $values);
    }
    public function getItemDescriptionWithAttributeNameAttribute() {
        $arr = [];
        foreach ($this->attribute_inventories as $attribute_inventory) {
            $arr[] = $attribute_inventory->attribute->name . " (" . $attribute_inventory->attribute_value->value . ")";
        }
        return implode(' - ', $arr);
    }

    public function getTitleAttribute() {
        return $this->product ? $this->product->name : $this->title;
    }

    public function getFullDescriptionAttribute() {
        $sku = empty($this->sku) ? null : "Sku ($this->sku)";
        return $sku != null ? "$this->title - $this->item_description_with_attribute_name - $sku" : $this->item_description_with_attribute_name;
    }
    public function getIsGoingToSaleAttribute() {
        return $this->offer_start != null && $this->offer_end > Carbon::now() && $this->offer_price < $this->sale_price;
    }

    public function hasOffer() {
        return ($this->offer_price < $this->sale_price) &&
            ($this->offer_start < Carbon::now()) &&
            ($this->offer_end > Carbon::now());
    }

    public function current_sale_price() {
        if ($this->hasOffer())
            return $this->offer_price;

        return $this->sale_price;
    }
}

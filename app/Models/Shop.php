<?php

namespace App\Models;

use App\Scopes\CustomScope;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model {
    use CustomScope;
    protected $table = 'shops';

    public function inventories() {
        return $this->hasMany(Inventory::class);
    }
}

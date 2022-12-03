<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    protected $table = 'product_view';

    protected $casts = [
        'updated_at' => 'timestamp'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    const STATUS_ORDERED                = 1;
    const STATUS_CANCELLED              = 2;
    const STATUS_CONFIRMED              = 3;
    const STATUS_CANCELLED_REQUEST      = 4;
    const STATUS_DELIVERING             = 5;
    const STATUS_DELIVERED              = 6;
    const STATUS_FULFILLED              = 7;

    const STATUS_WAITING_FOR_PAYMENT    = 8;    // Default
    const STATUS_PAYMENT_ERROR          = 9;
    const STATUS_RETURNED               = 10;

    const PAYMENT_STATUS_UNPAID             = 1;       // Default
    const PAYMENT_STATUS_PENDING            = 2;
    const PAYMENT_STATUS_PAID               = 3;      // All status before paid value consider as unpaid
    const PAYMENT_STATUS_INITIATED_REFUND   = 4;
    const PAYMENT_STATUS_PARTIALLY_REFUNDED = 5;
    const PAYMENT_STATUS_REFUNDED           = 6;

    const CANCELER_CUSTOMER = 0;
    const CANCELER_SHOP = 1;
    const CANCELER_ADMIN = 2;
    const CANCELER_SYSTEM = 3;
    const CANCELER_SHIPPING_SERVICE = 4;

    const DAY_ORDER_EXPIRED = 21;
    const OUT_OF_STOCK_CANCEL_REASON = 'out_of_stock';
    const DAY_ORDER_RECEIVED = 2;
}

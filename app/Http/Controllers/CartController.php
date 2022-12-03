<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller {
    public function getItemInCart($id) {
        $cart = Cart::findOrFail($id);
        $inventory_ids = $cart->inventories()->orderBy('cart_items.updated_at', 'desc')->pluck('id')->toArray();
        $shop_ids = $cart->cart_items()->orderBy('cart_items.updated_at', 'desc')->distinct('cart_items.shop_id')->pluck('shop_id')->toArray();
        $cart = Cart::with(['shops' => function ($q) use ($inventory_ids, $cart, $shop_ids) {
            return $q->whereRelationIn('inventories', 'inventories.id', $inventory_ids)
                ->with(['inventories' => function ($q) use ($inventory_ids, $cart) {
                    return $q->whereIn('id', $inventory_ids)->with(['cart_items' => function ($q) use ($cart) {
                        return $q->where('cart_id', $cart->id);
                    }, 'product' => function ($q) {
                        return $q->withoutGlobalScopes()->with('product_view');
                    }])->whereHas('product')->orderByField('id', $inventory_ids);
                }])->orderByField('shops.id', $shop_ids);
        }])->whereHas('shops')->findOrFail($id);
        return response()->json(new CartResource($cart));
    }

    public function addToCart(Request $request) {
        $customer_id = $request->customer_id;
        $inventory_ids = $request->inventory_ids;
        DB::beginTransaction();
        try {
            foreach ($inventory_ids as $id) {
                $item = Inventory::whereHas('product', function ($q) {
                    $q->available();
                })->find($id);

                if (empty($item)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Sản phẩm không còn hoạt động trên De Closet'
                    ], 404);
                }
                // if item not enough quantity
                if ($request->quantity > $item->stock_quantity) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Sản phẩm không đủ số lượng trên Decloset",
                        'status_code' => 404,
                        'success' => false,
                        'data' => ['item' => $item]
                    ], 404);
                }

                $old_cart = Cart::where('customer_id', $customer_id)->latest()->first();

                $qtt = $request->quantity ?? $item->min_order_quantity;
                $is_item_already_exist = false;

                // Check if the item is alrealy in the cart
                if ($old_cart) {
                    $item_in_cart = DB::table('cart_items')->where('cart_id', $old_cart->id)->where('inventory_id', $item->id)->first(); //->first();

                    //Begin - if item already exist in cart -Nhất Anh
                    if ($item_in_cart) {
                        $total_qtt = $qtt + $item_in_cart->quantity;

                        // if total quantity want to purchase > product quantity => return error

                        if ($total_qtt > $item->stock_quantity) {
                            DB::rollBack();
                            return response()->json([
                                'message' => "Sản phẩm không đủ số lượng trên Decloset",
                                'status_code' => 403,
                                'success' => false,
                                'data' => ['item' => $item]
                            ], 403);
                        }

                        $is_item_already_exist = true;
                    }
                }

                $unit_price = $item->current_sale_price();

                $cart = $old_cart ?? new Cart;
                $cart->shop_id = $item->shop_id;
                $cart->customer_id = $customer_id;
                $cart->ip_address = $request->ip();
                $cart->item_count = $old_cart ? ($is_item_already_exist ? $old_cart->item_count : $old_cart->item_count + 1) : 1; //Sửa lại cho đúng logic - Nhất Anh
                $cart->quantity = $old_cart ? ($old_cart->quantity + $qtt) : $qtt;

                if ($request->shipTo)
                    $cart->ship_to = $request->shipTo;

                $cart->total = $old_cart ? ($old_cart->total + ($qtt * $unit_price)) : $unit_price;

                $cart->save();

                // Makes item_description field
                $attributes = implode(' - ', $item->attribute_values->pluck('value')->toArray());
                // Prepare pivot data
                $cart_item_pivot_data = [];
                $cart_item_pivot_data[$item->id] = [
                    "shop_id" => $item->shop_id,
                    "item_name" => $item->name,
                    'inventory_id' => $item->id,
                    'item_description' => $item->name . ' - ' . $attributes . ' - ' . $item->condition,
                    'quantity' => isset($total_qtt) ? $total_qtt : $qtt,
                    'unit_price' => $unit_price,
                ];
                // Save cart items into pivot
                if (!empty($cart_item_pivot_data))
                    $cart->inventories()->syncWithoutDetaching($cart_item_pivot_data);
                DB::commit();
                return response()->json([
                    'message' => trans('api.item_added_to_cart'),
                    'status_code' => 200,
                    'success' => true,
                    'data' => [
                        'total_quantity' => $cart->quantity,
                        'total_amount' => $cart->grand_total
                    ]
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th;
        }
    }

    public function removeItem($id, $inventory_id) {
        CartItem::where('inventory_id', $inventory_id)->where('cart_id', $id)->delete();
        return response()->json([
            'message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'
        ]);
    }
}

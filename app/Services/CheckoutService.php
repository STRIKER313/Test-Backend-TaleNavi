<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function checkout(User $user): Order
    {
        return DB::transaction(function () use ($user): Order {
            User::lockForUpdate()->findOrFail($user->getKey());
            $cart = $user->cartItems()->get();
            abort_if($cart->isEmpty(), 409, 'Keranjang kosong.');

            $products = Product::whereKey($cart->pluck('product_id')->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = BigDecimal::of('0.00');
            $items = [];

            foreach ($cart as $item) {
                $product = $products->get($item->product_id);
                abort_if(
                    $item->quantity > $product->stock,
                    409,
                    "Stok {$product->title} tidak mencukupi. Stok tersedia: {$product->stock}.",
                );

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                ];
                $total = $total->plus(BigDecimal::of($product->price)->multipliedBy($item->quantity));
            }

            abort_if($total->isGreaterThan('99999999999999.99'), 409, 'Total pesanan melebihi batas yang dapat diproses.');

            $order = $user->orders()->create(['total_price' => (string) $total, 'status' => 'placed']);
            $order->items()->createMany($items);

            foreach ($items as $item) {
                $products->get($item['product_id'])->decrement('stock', $item['quantity']);
            }

            $user->cartItems()->delete();

            return $order->load('items.product:id,title');
        }, 3);
    }
}

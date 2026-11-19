<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function contents(User $user): array
    {
        $items = $user->cartItems()->with('product')->orderBy('id')->get()->map($this->itemData(...));
        $total = $items->reduce(
            fn (BigDecimal $total, array $item) => $total->plus($item['subtotal']),
            BigDecimal::of('0.00'),
        );

        return ['items' => $items, 'total_price' => (string) $total];
    }

    public function add(User $user, int $productId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($user, $productId, $quantity): CartItem {
            $this->lockUser($user);
            $product = Product::lockForUpdate()->find($productId);

            if ($product === null) {
                throw ValidationException::withMessages(['product_id' => ['Produk tidak ditemukan.']]);
            }

            $item = $user->cartItems()->firstOrNew(['product_id' => $productId]);

            return $this->saveQuantity($item, $product, ($item->quantity ?? 0) + $quantity);
        });
    }

    public function update(User $user, int $id, int $quantity): CartItem
    {
        return DB::transaction(function () use ($user, $id, $quantity): CartItem {
            $this->lockUser($user);
            $item = $user->cartItems()->findOrFail($id);
            $product = Product::lockForUpdate()->findOrFail($item->product_id);

            return $this->saveQuantity($item, $product, $quantity);
        });
    }

    public function remove(User $user, int $id): void
    {
        DB::transaction(function () use ($user, $id): void {
            $this->lockUser($user);
            $user->cartItems()->findOrFail($id)->delete();
        });
    }

    public function itemData(CartItem $item): array
    {
        $subtotal = BigDecimal::of($item->product->price)->multipliedBy($item->quantity);

        return $item->only('id', 'product_id', 'quantity') + [
            'product' => $item->product,
            'subtotal' => (string) $subtotal,
        ];
    }

    private function lockUser(User $user): void
    {
        User::lockForUpdate()->findOrFail($user->getKey());
    }

    private function saveQuantity(CartItem $item, Product $product, int $quantity): CartItem
    {
        abort_if($quantity > $product->stock, 409, "Stok {$product->title} tidak mencukupi. Stok tersedia: {$product->stock}.");
        $item->quantity = $quantity;
        $item->save();

        return $item->setRelation('product', $product);
    }
}

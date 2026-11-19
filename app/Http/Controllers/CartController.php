<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController
{
    public function __construct(private CartService $cart)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return $this->response('Keranjang berhasil diambil.', $this->cart->contents($request->user()));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'between:1,2147483647'],
        ], $this->validationMessages());

        $item = $this->cart->add($request->user(), (int) $data['product_id'], (int) $data['quantity']);

        return $this->response(
            'Produk berhasil ditambahkan ke keranjang.',
            $this->cart->itemData($item),
            $item->wasRecentlyCreated ? 201 : 200,
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $id = $this->itemId($id);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'between:1,2147483647'],
        ], $this->validationMessages());

        $item = $this->cart->update($request->user(), $id, (int) $data['quantity']);

        return $this->response('Jumlah item berhasil diubah.', $this->cart->itemData($item));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->cart->remove($request->user(), $this->itemId($id));

        return $this->response('Item berhasil dihapus dari keranjang.', null);
    }

    private function itemId(string $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }

    private function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'product_id.min' => 'ID produk minimal 1.',
            'quantity.between' => 'Jumlah item harus antara 1 dan :max.',
        ];
    }

    private function response(string $message, ?array $data, int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }
}

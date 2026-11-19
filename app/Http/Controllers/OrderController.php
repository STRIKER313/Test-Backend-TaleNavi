<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Riwayat pesanan berhasil diambil.',
            'data' => $request->user()->orders()->latest()->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request, CheckoutService $checkout): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat.',
            'data' => $checkout->checkout($request->user()),
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => $request->user()->orders()->with('items.product:id,title')->findOrFail($id),
        ]);
    }
}

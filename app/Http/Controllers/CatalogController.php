<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CatalogController
{
    public function products(Request $request): JsonResponse
    {
        $filters = Validator::make($request->query(), [
            'page' => ['sometimes', 'required', 'integer', 'min:1', 'max:2147483647'],
            'limit' => ['sometimes', 'required', 'integer', 'between:1,50'],
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'required', Rule::in(['price_asc', 'price_desc', 'newest'])],
        ], [
            'required' => 'Parameter :attribute wajib diisi jika dikirim.',
            'integer' => 'Parameter :attribute harus berupa bilangan bulat.',
            'string' => 'Parameter :attribute harus berupa teks.',
            'min.numeric' => 'Parameter :attribute minimal :min.',
            'max.numeric' => 'Parameter :attribute maksimal :max.',
            'max.string' => 'Parameter :attribute maksimal :max karakter.',
            'limit.between' => 'Parameter limit harus antara 1 dan 50.',
            'sort.in' => 'Parameter sort harus price_asc, price_desc, atau newest.',
        ])->validate();

        $query = Product::with('category');

        if (isset($filters['q'])) {
            $title = addcslashes($filters['q'], '\\%_');
            $query->whereLike('title', '%'.$title.'%');
        }

        if (isset($filters['category'])) {
            $query->whereRelation('category', 'slug', $filters['category']);
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            'newest' => $query->latest()->orderByDesc('id'),
        };

        $products = $query->paginate(
            perPage: (int) ($filters['limit'] ?? 10),
            page: (int) ($filters['page'] ?? 1),
        );

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk berhasil diambil.',
            'data' => $products->items(),
            'meta' => [
                'page' => $products->currentPage(),
                'limit' => $products->perPage(),
                'total' => $products->total(),
                'total_pages' => $products->lastPage(),
            ],
        ]);
    }

    public function product(string $id): JsonResponse
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil.',
            'data' => Product::with('category')->findOrFail($id),
        ]);
    }

    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori berhasil diambil.',
            'data' => Category::orderBy('name')->orderBy('id')->get(),
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'smartphones' => ['Smartphones', [
                ['Astra Phone Mini', 1499000, 2, 'Entry-level smartphone.'],
                ['Astra Phone SE', 1999000, 11, 'Compact smartphone.'],
                ['Astra Phone Air', 2799000, 12, 'Everyday smartphone.'],
                ['Astra Phone 5G', 3199000, 13, 'Wireless-ready smartphone.'],
                ['Astra Phone Pro', 4299000, 14, 'Upgraded smartphone.'],
                ['Astra Phone Ultra', 5999000, 15, 'Premium smartphone.'],
            ]],
            'audio' => ['Audio', [
                ['Pulse Wired Earbuds', 89000, 2, 'Entry-level audio equipment.'],
                ['Pulse Bluetooth Speaker', 249000, 11, 'Compact audio equipment.'],
                ['Pulse Studio Headphones', 449000, 12, 'Everyday audio equipment.'],
                ['Pulse USB Microphone', 579000, 13, 'Wireless-ready audio equipment.'],
                ['Pulse ANC Earbuds', 699000, 14, 'Upgraded audio equipment.'],
                ['Pulse Soundbar', 899000, 15, 'Premium audio equipment.'],
            ]],
            'computers' => ['Computers', [
                ['Orbit Mini PC', 3499000, 2, 'Entry-level computer.'],
                ['Orbit Laptop 14', 5899000, 11, 'Compact computer.'],
                ['Orbit Laptop 15', 6499000, 12, 'Everyday computer.'],
                ['Orbit Chromebook', 4299000, 13, 'Wireless-ready computer.'],
                ['Orbit All-in-One', 7399000, 14, 'Upgraded computer.'],
                ['Orbit Workstation', 12999000, 15, 'Premium computer.'],
            ]],
            'accessories' => ['Accessories', [
                ['Link USB-C Cable', 59000, 2, 'Entry-level desk accessory.'],
                ['Link Laptop Stand', 159000, 11, 'Compact desk accessory.'],
                ['Link Wireless Mouse', 189000, 12, 'Everyday desk accessory.'],
                ['Link USB-C Hub', 299000, 13, 'Wireless-ready desk accessory.'],
                ['Link Keyboard', 389000, 14, 'Upgraded desk accessory.'],
                ['Link Power Bank', 479000, 15, 'Premium desk accessory.'],
            ]],
            'wearables' => ['Wearables', [
                ['Tempo Fitness Band', 239000, 2, 'Entry-level wearable.'],
                ['Tempo Watch Lite', 399000, 11, 'Compact wearable.'],
                ['Tempo Watch Active', 599000, 12, 'Everyday wearable.'],
                ['Tempo Watch GPS', 799000, 13, 'Wireless-ready wearable.'],
                ['Tempo Watch Pro', 999000, 14, 'Upgraded wearable.'],
                ['Tempo Watch Classic', 1299000, 15, 'Premium wearable.'],
            ]],
        ];

        foreach ($catalog as $slug => [$name, $products]) {
            DB::table('categories')->insertOrIgnore(['slug' => $slug, 'name' => $name]);
            $categoryId = DB::table('categories')->where('slug', $slug)->value('id');
            $timestamp = now();

            $rows = array_map(fn (array $product) => [
                'category_id' => $categoryId,
                'title' => $product[0],
                'price' => $product[1],
                'stock' => $product[2],
                'description' => $product[3],
                'image_url' => 'https://placehold.co/600x400?text='.rawurlencode(Str::headline($product[0])),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], $products);

            DB::table('products')->insertOrIgnore($rows);
        }
    }
}

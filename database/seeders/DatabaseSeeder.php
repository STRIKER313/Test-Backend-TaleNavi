<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('catalog.demo_email');
        $password = config('catalog.demo_password');

        if (! is_string($email) || strlen($email) > 255 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Isi DEMO_USER_EMAIL dengan alamat email yang valid sebelum menjalankan seeder.');
        }

        if (! is_string($password) || mb_strlen($password) < 8 || strlen($password) > 72 || str_contains($password, chr(0))) {
            throw new \RuntimeException('Isi DEMO_USER_PASSWORD dengan minimal 8 karakter dan maksimal 72 byte, tanpa karakter null.');
        }

        DB::transaction(function () use ($email, $password) {
            DB::table('users')->insertOrIgnore([
                'name' => 'Demo Buyer',
                'email' => strtolower($email),
                'password' => Hash::make($password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->call(CatalogSeeder::class);
        });
    }
}

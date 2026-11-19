# TaleNavi Product Catalog & Order API

Proyek REST API katalog produk dan pesanan ini menggunakan Laravel 12, PostgreSQL 16, dan Laravel Sanctum. PostgreSQL dipilih karena mendukung foreign key, batasan CHECK, transaksi, dan penguncian baris untuk menjaga konsistensi stok saat checkout. Dokumentasi dan demo API menggunakan Postman.

## Penyiapan database lokal

Siapkan PHP 8.2 atau yang lebih baru dengan ekstensi PDO PostgreSQL yang aktif, Composer, dan server PostgreSQL yang sedang berjalan. Buat database kosong bernama `talenavi_catalog` atau sesuaikan `DB_DATABASE` dengan nama database lokal.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Atur `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai konfigurasi PostgreSQL lokal. Isi `DEMO_USER_EMAIL` dengan alamat email yang valid dan `DEMO_USER_PASSWORD` dengan kata sandi khusus akun demo yang panjangnya minimal 8 karakter dan maksimal 72 byte.

```powershell
php artisan migrate --seed
php artisan serve
```

Jalankan `php artisan db:seed` jika ingin menjalankan ulang seeder. Seeder mempertahankan stok, harga, dan kata sandi pengguna yang sudah tersimpan tanpa menduplikasi data. Status aplikasi lokal dapat diperiksa di `http://localhost:8000/up`. Jika memakai port berbeda, atur `SERVER_PORT` dan `APP_URL` di `.env` serta sesuaikan `base_url` di Postman.

## Desain database

- Satu kategori dapat memiliki banyak produk.
- Satu pengguna dapat memiliki banyak item keranjang, dengan maksimal satu baris untuk setiap produk.
- Satu pengguna dapat memiliki banyak pesanan. Setiap pesanan berisi satu atau lebih item.
- Setiap item pesanan merujuk pada produk dan menyimpan harga satuannya saat checkout.
- Sanctum menyimpan token akses di tabel `personal_access_tokens`.

Seeder demo menambahkan lima kategori, masing-masing dengan enam produk, serta satu akun pembeli. Batasan CHECK pada database mencegah stok dan harga bernilai negatif serta kuantitas item kurang dari satu.

## Dokumentasi dan demo API

Impor [Postman Collection](postman/TaleNavi.postman_collection.json) dan [Postman Environment](postman/TaleNavi.postman_environment.json). Pilih environment **TaleNavi Lokal** dan pastikan `base_url` menunjuk ke server Laravel. Nilai bawaannya adalah `http://localhost:8000`. Setiap request memiliki contoh respons yang dapat dibuka di Postman.

Jalankan request berikut secara berurutan untuk demo.

1. **Daftar akun**, lalu **Masuk**. Postman membuat email demo baru dan menyimpan token hasil login. Token berlaku 24 jam sejak login.
2. **Daftar produk**, lalu **Detail produk** jika ingin melihat salah satu produk. Request daftar produk mengisi `product_id` dari produk pertama yang masih tersedia. Untuk mencoba pencarian, filter, atau pengurutan, aktifkan parameter yang diinginkan pada tab **Params**.
3. **Tambah produk**, lalu **Isi keranjang** untuk melihat subtotal item dan total harga. Request tambah produk menyimpan `cart_item_id` untuk request ubah dan hapus.
4. **Checkout**, lalu **Riwayat pesanan** dan **Detail pesanan**. Checkout menyimpan `order_id` untuk request detail.

Untuk mencoba respons gagal, pilih **No Auth** di tab **Authorization** pada request Profil akun dan kirim untuk melihat `401`. Kembalikan ke **Inherit auth from parent** setelah percobaan. Aktifkan parameter `limit` dan isi `51` untuk melihat `422`, gunakan ID produk yang tidak ada untuk melihat `404`, atau ulangi checkout dengan keranjang kosong untuk melihat `409`.

Untuk mencoba checkout dengan stok kurang, gunakan dua akun demo dan pilih produk yang stoknya sedikit. Masukkan seluruh stok yang tersedia ke keranjang masing-masing akun sebelum checkout. Checkout akun pertama, lalu checkout akun kedua. Akun kedua mendapat `409` yang menyebutkan nama produk, dan itemnya tetap ada di keranjang.

## Struktur kode

- `routes/api.php` mendefinisikan endpoint dan middleware autentikasi.
- `app/Http/Controllers` menangani request, validasi, dan respons JSON.
- `app/Models` mendefinisikan model Eloquent dan relasi tabel.
- `app/Services` menangani aturan keranjang dan transaksi checkout.
- `database/migrations` dan `database/seeders` menyiapkan skema dan data demo.

## Cara kerja checkout

Backend mengunci baris pengguna agar perubahan keranjang dan checkout dari akun yang sama berjalan bergantian. Backend kemudian mengunci baris produk menurut urutan ID, memeriksa stok terbaru, menyimpan pesanan beserta harga satuan saat checkout, mengurangi stok, dan mengosongkan keranjang. Seluruh perubahan berjalan dalam satu transaksi PostgreSQL. Jika salah satu langkah gagal, perubahan dibatalkan.

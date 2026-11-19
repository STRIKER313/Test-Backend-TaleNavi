<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->integer('stock');
            $table->string('image_url', 2048);
            $table->timestamps();
            $table->unique(['category_id', 'title']);
        });

        DB::statement('ALTER TABLE products ADD CONSTRAINT products_valid_values CHECK (price >= 0 AND stock >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

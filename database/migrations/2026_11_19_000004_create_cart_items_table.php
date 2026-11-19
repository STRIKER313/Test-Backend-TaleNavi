<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->integer('quantity');
            $table->unique(['user_id', 'product_id']);
        });

        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT cart_items_valid_values CHECK (quantity >= 1)');
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};

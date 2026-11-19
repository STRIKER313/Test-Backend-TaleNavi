<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('total_price', 16, 2);
            $table->string('status')->default('placed');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_valid_values CHECK (total_price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

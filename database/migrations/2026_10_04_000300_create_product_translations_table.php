<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Non-English product copy, one row per (product, locale). English stays on
 * `products` itself (it is the fallback locale and what order snapshots,
 * admin search, and the API's `name` field already use), so adding Hindi
 * later is new rows here plus a lang file — no schema change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'locale']);
            $table->index(['locale', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_translations');
    }
};

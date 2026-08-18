<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_trending_products', function (Blueprint $table) {
            $table->id();
            $table->string('supplier')->default('cj_dropshipping');
            $table->string('supplier_product_id')->index();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('sales_count')->nullable(); // señal de ventas del proveedor si la expone
            $table->unsignedInteger('rank')->nullable(); // posición en el ranking trending del proveedor
            $table->string('image_url')->nullable();
            $table->string('product_url')->nullable();
            $table->date('captured_on');
            $table->timestamps();

            $table->unique(['supplier', 'supplier_product_id', 'captured_on'], 'stp_supplier_product_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_trending_products');
    }
};

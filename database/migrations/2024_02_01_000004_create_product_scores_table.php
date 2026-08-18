<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une la señal de Google Trends (por keyword) con la señal del proveedor
        // (por producto) en un único ranking consultable desde el dashboard.
        Schema::create('product_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_keyword_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_trending_product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label'); // nombre representativo del producto/tendencia
            $table->decimal('trend_growth_score', 8, 2)->default(0); // variación % de interés en Trends
            $table->decimal('supplier_signal_score', 8, 2)->default(0); // normalizado desde rank/sales_count
            $table->decimal('total_score', 8, 2)->default(0); // combinado, lo que ordena el dashboard
            $table->date('computed_on');
            $table->timestamps();

            $table->index(['computed_on', 'total_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_scores');
    }
};

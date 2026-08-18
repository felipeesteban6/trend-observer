<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trend_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_keyword_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('interest')->default(0); // 0-100, escala de Google Trends
            $table->timestamps();

            $table->unique(['search_keyword_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trend_snapshots');
    }
};

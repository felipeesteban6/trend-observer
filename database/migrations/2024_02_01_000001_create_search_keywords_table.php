<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('term'); // ej. "mochila anti robo"
            $table->string('language', 5)->default('es'); // es | en
            $table->string('geo', 5)->nullable(); // CL, MX, US, '' = mundial
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['term', 'language', 'geo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_keywords');
    }
};

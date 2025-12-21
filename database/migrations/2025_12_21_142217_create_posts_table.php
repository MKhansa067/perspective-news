<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Penulis
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Kategori
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('image')->nullable(); // Path gambar
            $table->text('description'); // Ringkasan singkat
            $table->longText('content'); // Isi berita (HTML)
            $table->unsignedBigInteger('views')->default(0); // Counter views
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

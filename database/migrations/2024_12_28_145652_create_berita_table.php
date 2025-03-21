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
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('berita_id')->unique();
            $table->string('author');
            $table->string('title');
            $table->string('subtitle');
            $table->longText('description');
            $table->string('images');
            $table->json('tags')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();

            //FK

            $table->index('author');
            $table->foreign('author')->references('name')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};

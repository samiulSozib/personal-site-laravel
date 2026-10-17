<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('excerpt');
            $table->date('date');
            $table->string('category');
            $table->json('tags');
            $table->string('cover')->nullable();
            $table->boolean('published')->default(false);
            $table->json('content');
            $table->timestamps();

            $table->index(['published', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->string('company_url')->nullable();
            $table->json('role');
            $table->string('employment_type');
            $table->json('location')->nullable();
            $table->date('start_date');
            // Null while the role is current.
            $table->date('end_date')->nullable();
            $table->json('summary');
            $table->json('highlights');
            $table->json('stack');
            $table->timestamps();

            $table->index('start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};

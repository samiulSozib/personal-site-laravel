<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fields for the project detail page. All optional, so existing projects keep working.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('role')->nullable()->after('summary');
            $table->json('period')->nullable()->after('role');
            $table->json('highlights')->nullable()->after('period');
            $table->json('content')->nullable()->after('highlights');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['role', 'period', 'highlights', 'content']);
        });
    }
};

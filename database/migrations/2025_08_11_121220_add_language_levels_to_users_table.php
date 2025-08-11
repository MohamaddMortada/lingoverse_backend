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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('english_level')->default(1)->after('remember_token');
            $table->integer('french_level')->default(1)->after('english_level');
            $table->integer('arabic_level')->default(1)->after('french_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['english_level', 'french_level', 'arabic_level']);

        });
    }
};

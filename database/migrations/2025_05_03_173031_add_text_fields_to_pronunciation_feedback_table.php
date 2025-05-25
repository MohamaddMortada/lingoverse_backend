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
        Schema::table('pronunciation_feedback', function (Blueprint $table) {
            $table->text('expected_text')->nullable();
        $table->text('spoken_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pronunciation_feedback', function (Blueprint $table) {
            //
        });
    }
};

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
        // Membuat index pengganti untuk user_id
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('user_id', 'reviews_user_id_index');
        });

        // Menghapus unique constraint sebelumnya
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_user_id_movie_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mengembalikan unique constraint sebelumnya
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'movie_id']);
        });

        // Menghapus index pengganti
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_user_id_index');
        });
    }
};
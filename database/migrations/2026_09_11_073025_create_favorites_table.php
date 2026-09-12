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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('tmdb_movie_id'); // ID from TMDB, not our own DB
            $table->string('title')->nullable();          // optional: cache movie title
            $table->string('poster_path')->nullable();     // optional: cache poster
            $table->timestamps();

            $table->unique(['user_id', 'tmdb_movie_id']); // prevent duplicate favorites
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};

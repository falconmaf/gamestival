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
        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('new');
            $table->integer('max_players')->default(4);
            $table->integer('grid_size')->default(4);
            $table->integer('current_player_id')->nullable();
            $table->json('card_positions');
            $table->integer('turn_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_matches');
    }
};

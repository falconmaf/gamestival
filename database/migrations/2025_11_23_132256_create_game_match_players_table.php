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
        Schema::create('game_match_players', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id');
            $table->integer('position');
            $table->string('card_value');
            $table->boolean('is_flipped')->default(false);
            $table->boolean('is_matched')->default(false);
            $table->integer('matched_by_player_id')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_match_players');
    }
};

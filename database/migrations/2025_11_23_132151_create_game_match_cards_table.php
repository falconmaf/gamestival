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
        Schema::create('game_match_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id');
            $table->integer('user_id');
            $table->integer('score')->default(0);
            $table->integer('turn_order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['game_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_match_cards');
    }
};

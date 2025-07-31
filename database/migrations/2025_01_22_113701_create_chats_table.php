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
        //     Schema::create('chats', function (Blueprint $table) {
        //         $table->id();
        //         $table->foreignId('user_one_id')->constrained('users')->onDelete('cascade')->index();
        //         $table->foreignId('user_two_id')->constrained('users')->onDelete('cascade')->index();
        //         $table->timestamps();

        //         $table->unique(['user_one_id', 'user_two_id']);
        //     });
        // }
        Schema::create('chats', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_one_id');
            $table->unsignedBigInteger('user_two_id');

            $table->timestamps();

            $table->unique(['user_one_id', 'user_two_id']);

            // ✅ Explicit constraint names to avoid collision
            $table->foreign('user_one_id', 'fk_chats_user_one')
                ->references('id')->on('users')->onDelete('cascade');

            $table->foreign('user_two_id', 'fk_chats_user_two')
                ->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};

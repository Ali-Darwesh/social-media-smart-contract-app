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
        Schema::create('contract_user', function (Blueprint $table) {
            $table->id();

            // Relation to contracts table
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');

            // Still keep relation to users table (optional if you want auth-linked users)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('user_address')->nullable();
            $table->enum('role', ['client', 'service_provider',])->default('client');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_user');
    }
};

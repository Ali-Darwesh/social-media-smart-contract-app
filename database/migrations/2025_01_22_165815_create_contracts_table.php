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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('address');               // Wallet address of client
            $table->string('contract_address')->unique()->nullable();   // On-chain contract address
            $table->string('client')->nullable();               // Wallet address of client
            $table->string('serviceProvider')->nullable();     // Wallet address of service provider
            $table->unsignedBigInteger('totalAmount')->nullable();
            $table->enum('status', ['Draft', 'PendingApproval', 'Active', 'Rejected', 'Completed'])->default('Draft');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

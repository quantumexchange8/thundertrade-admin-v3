<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_number');
            $table->decimal('deposit_balance', 13, 2)->default(0);
            $table->decimal('gross_deposit', 13, 2)->default(0);
            $table->decimal('gross_withdrawal', 13, 2)->default(0);
            $table->string('wallet_address')->nullable();
            $table->string('type');
            $table->foreignId('merchant_id')->constrained('merchants');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_wallets');
    }
};

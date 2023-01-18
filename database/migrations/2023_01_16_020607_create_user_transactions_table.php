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
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->tinyInteger('status')->default(0);
            $table->string('transaction_type');
            $table->string('address');
            $table->string('currency');
            $table->decimal('amount', 13, 2)->default(0);
            $table->decimal('charges', 13, 2)->default(0);
            $table->decimal('total', 13, 2)->default(0);
            $table->foreignId('wallet_id')->constrained('merchant_wallets');
            $table->string('transaction_no');
            $table->string('TxID')->nullable();
            $table->foreignId('merchant_id')->nullable()->constrained('merchants');
            $table->string('approval_reason')->nullable();
            $table->date('approval_date')->nullable();
            $table->string('approval_by')->nullable();
            $table->string('receipt')->nullable();
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('user_transactions');
    }
};

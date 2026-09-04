<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider');
            $table->string('provider_transaction_id');
            $table->string('payment_reference')->nullable();
            $table->decimal('amount', 12, 2);
            $table->timestamp('transaction_date')->nullable();
            $table->string('transfer_type')->nullable();
            $table->string('description', 1000)->nullable();
            $table->string('status');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_transaction_id']);
            $table->index('payment_reference');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

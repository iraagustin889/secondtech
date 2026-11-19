<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('gateway')->nullable()->after('method');
            $table->string('gateway_order_id')->nullable()->unique()->after('gateway');
            $table->string('gateway_token')->nullable()->after('gateway_order_id');
            $table->text('gateway_redirect_url')->nullable()->after('gateway_token');
            $table->string('gateway_transaction_id')->nullable()->after('gateway_redirect_url');
            $table->json('gateway_response')->nullable()->after('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique(['gateway_order_id']);
            $table->dropColumn([
                'gateway',
                'gateway_order_id',
                'gateway_token',
                'gateway_redirect_url',
                'gateway_transaction_id',
                'gateway_response',
            ]);
        });
    }
};

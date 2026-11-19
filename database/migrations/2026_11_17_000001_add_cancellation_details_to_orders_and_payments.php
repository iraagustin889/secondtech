<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');
            $table->text('cancellation_note')->nullable()->after('cancellation_reason');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');
            $table->timestamp('refunded_at')->nullable()->after('cancelled_at');
            $table->timestamp('released_at')->nullable()->after('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn(['cancelled_at', 'refunded_at', 'released_at']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['cancellation_reason', 'cancellation_note']);
        });
    }
};

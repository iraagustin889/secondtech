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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->unique()->after('id');
            $table->string('shipping_method')->nullable()->after('status');
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('service_fee');
            $table->string('recipient_name')->nullable()->after('shipping_method');
            $table->string('recipient_phone', 30)->nullable()->after('recipient_name');
            $table->text('recipient_address')->nullable()->after('recipient_phone');
            $table->string('recipient_location')->nullable()->after('recipient_address');
            $table->timestamp('cancelled_at')->nullable()->after('status');

            $table->index(['buyer_id', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['buyer_id', 'status', 'created_at']);
            $table->dropUnique(['order_number']);
            $table->dropColumn([
                'order_number',
                'shipping_method',
                'shipping_cost',
                'recipient_name',
                'recipient_phone',
                'recipient_address',
                'recipient_location',
                'cancelled_at',
            ]);
        });
    }
};

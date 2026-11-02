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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('method');
            $table->string('status')->default('waiting');
            $table->string('courier')->nullable();
            $table->string('service')->nullable();
            $table->unsignedBigInteger('origin_id')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->string('destination_name')->nullable();
            $table->unsignedInteger('weight')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('estimated_delivery')->nullable();
            $table->string('resi')->nullable();
            $table->string('tracking_status')->nullable();
            $table->timestamp('tracking_checked_at')->nullable();
            $table->text('cod_location')->nullable();
            $table->timestamp('cod_meeting_at')->nullable();
            $table->timestamp('seller_cod_confirmed_at')->nullable();
            $table->timestamp('buyer_cod_confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};

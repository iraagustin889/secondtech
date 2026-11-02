<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_fee_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('type');             // nominal | percentage
            $table->decimal('value', 12, 2);    // Rp atau persen
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_fee_configurations');
    }
};
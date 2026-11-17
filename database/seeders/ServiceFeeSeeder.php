<?php

namespace Database\Seeders;

use App\Models\ServiceFeeConfiguration;
use Illuminate\Database\Seeder;

class ServiceFeeSeeder extends Seeder
{
    public function run(): void
    {
        if (ServiceFeeConfiguration::query()->where('is_active', true)->exists()) {
            return;
        }

        ServiceFeeConfiguration::create([
            'type' => 'nominal',
            'value' => 2_500,
            'is_active' => true,
        ]);
    }
}

<?php

namespace Tests\Unit;

use App\Models\ServiceFeeConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceFeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_config_returns_zero(): void
    {
        $this->assertSame(0, ServiceFeeConfiguration::calculate(100000));
    }

    public function test_percentage_fee(): void
    {
        ServiceFeeConfiguration::create(['type' => 'percentage', 'value' => 5, 'is_active' => true]);

        $this->assertSame(5000, ServiceFeeConfiguration::calculate(100000));
    }

    public function test_nominal_fee(): void
    {
        ServiceFeeConfiguration::create(['type' => 'nominal', 'value' => 2500, 'is_active' => true]);

        $this->assertSame(2500, ServiceFeeConfiguration::calculate(100000));
    }

    public function test_only_active_config_is_used(): void
    {
        ServiceFeeConfiguration::create(['type' => 'nominal', 'value' => 9999, 'is_active' => false]);
        ServiceFeeConfiguration::create(['type' => 'percentage', 'value' => 10, 'is_active' => true]);

        $this->assertSame(10000, ServiceFeeConfiguration::calculate(100000));
    }
}
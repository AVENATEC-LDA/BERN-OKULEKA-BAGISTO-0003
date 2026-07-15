<?php

namespace Webkul\FleetShipping\Tests\Unit;

use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\TestCase;
use Webkul\FleetShipping\Providers\FleetShippingServiceProvider;

class FleetServiceProviderTest extends TestCase
{
    public function test_service_provider_can_be_instantiated(): void
    {
        $application = $this->createMock(Application::class);
        $provider = new FleetShippingServiceProvider($application);

        $this->assertInstanceOf(FleetShippingServiceProvider::class, $provider);
    }
}

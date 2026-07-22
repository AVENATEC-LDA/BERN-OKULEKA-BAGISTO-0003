<?php

namespace Webkul\FleetShipping\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\FleetShipping\Models\FleetOrder;
use Webkul\FleetShipping\Models\FleetQuote;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        FleetOrder::class,
        FleetQuote::class,
    ];
}

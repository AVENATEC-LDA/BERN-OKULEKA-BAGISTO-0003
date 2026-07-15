<?php

declare(strict_types=1);

namespace Webkul\OpenGraphMeta\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Webkul\OpenGraphMeta\Services\OpenGraphMetaService;
use Webkul\Theme\ViewRenderEventManager;

class OpenGraphMetaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/opengraph.php', 'opengraph');

        $this->app->singleton(OpenGraphMetaService::class, function () {
            return new OpenGraphMetaService();
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'opengraph-meta');

        $this->publishes([
            __DIR__ . '/../Config/opengraph.php' => config_path('opengraph.php'),
        ], 'opengraph-meta-config');

        Event::listen('bagisto.shop.layout.head.after', function (ViewRenderEventManager $manager) {
            $manager->addTemplate('opengraph-meta::meta');
        });

        View::composer('shop::products.view', function ($view) {
            $product = $view->getData()['product'] ?? null;

            if ($product) {
                app(OpenGraphMetaService::class)->setFromEntity($product);
            }
        });

        View::composer('shop::categories.view', function ($view) {
            $category = $view->getData()['category'] ?? null;

            if ($category) {
                app(OpenGraphMetaService::class)->setFromEntity($category);
            }
        });
    }
}

<?php

declare(strict_types=1);

namespace Alifuz\AlifSearch\Providers;

use Illuminate\Support\ServiceProvider;

class ElasticSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/elasticsearch.php',
            'elasticsearch'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/elasticsearch.php' => config_path('elasticsearch.php'),
            ], 'elasticsearch-config');
        }
    }
}

<?php

namespace Statamic\SeoPro;

use Illuminate\Support\Facades\Event;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;
use Statamic\SeoPro;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        SeoPro\Tags\SeoProTags::class,
    ];

    protected $fieldtypes = [
        SeoPro\Fieldtypes\SeoProFieldtype::class,
        SeoPro\Fieldtypes\SourceFieldtype::class,
    ];

    protected $widgets = [
        SeoPro\Widgets\SeoProWidget::class,
    ];

    protected $scripts = [
        __DIR__.'/../resources/dist/js/cp.js',
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    public function boot()
    {
        parent::boot();

        $this
            ->bootAddonConfig()
            ->bootAddonViews()
            ->bootAddonTranslations()
            ->bootAddonNav()
            ->bootAddonSubscriber()
            ->bootAddonGlidePresets()
            ->bootAddonCommands();
    }

    protected function bootAddonConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/seo-pro.php', 'statamic.seo-pro');

        $this->publishes([
            __DIR__.'/../config/seo-pro.php' => config_path('statamic/seo-pro.php'),
        ], 'seo-pro-config');

        return $this;
    }

    protected function bootAddonViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views/generated', 'seo-pro');

        $this->publishes([
            __DIR__.'/../resources/views/generated' => resource_path('views/vendor/seo-pro'),
        ], 'seo-pro-views');

        return $this;
    }

    protected function bootAddonTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'seo-pro');

        return $this;
    }

    protected function bootAddonNav()
    {
        Nav::extend(function ($nav) {
            $nav->tools('SEO Pro')
                ->route('seo-pro.index')
                ->icon('seo-search-graph')
                ->active('seo-pro')
                ->children([
                    'Reports' => cp_route('seo-pro.reports.index'),
                    'Site Defaults' => cp_route('seo-pro.site-defaults.edit'),
                    'Section Defaults' => cp_route('seo-pro.section-defaults.index'),
                ]);
        });

        return $this;
    }

    protected function bootAddonSubscriber()
    {
        Event::subscribe(Subscriber::class);

        return $this;
    }

    protected function bootAddonGlidePresets()
    {
        $server = app(\League\Glide\Server::class);

        $server->setPresets($server->getPresets() + [
            'seo' => [
                'w' => config('statamic.seo-pro.assets.open_graph_preset.w'),
                'h' => config('statamic.seo-pro.assets.open_graph_preset.h'),
                'fit' => 'crop',
            ],
        ]);

        return $this;
    }

    protected function bootAddonCommands()
    {
        $this->commands([
            SeoPro\Commands\GenerateReportCommand::class,
        ]);
    }
}

<?php

namespace Modules\SsRemoteResponse\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class SsRemoteResponseServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Mailbox instance.
     *
     * @var Mailbox|null
     */
    private $mailbox = null;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Add module's JS file to the application layout.
        \Eventy::addFilter('javascripts', function($javascripts) {
            array_push($javascripts, \Module::getPublicPath("ssremoteresponse").'/js/module.js');
            return $javascripts;
        });

        // Add module's CSS file to the application layout.
        \Eventy::addFilter('stylesheets', function($stylesheets) {
            array_push($stylesheets, \Module::getPublicPath("ssremoteresponse").'/css/module.css');
            return $stylesheets;
        });

        //catch the mailbox for the current request
        \Eventy::addFilter('mailbox.show_buttons', function($show, $mailbox){
            $this->mailbox =$mailbox;
            return $show;
        }, 20 , 2);

        // JavaScript in the bottom
        \Eventy::addAction('javascript', function() {
            $version = \Module::find('ssremoteresponse')->get('version');
            $updateAvailable = __('Update available for module ');
            $send = __("Send");

            echo "const remoteResponseData = {" .
                    "'updateAvailable': '{$updateAvailable}'," .
                    "'version': '{$version}'," .
                    "'send': `{$send}`," .
                "};";
            echo 'remoteResponseInit();';
        });

        \Eventy::addAction('mailboxes.settings.menu', function($mailbox) {
            if (auth()->user()->isAdmin()) {
                echo \View::make('ssremoteresponse::partials/settings_menu', ['mailbox' => $mailbox])->render();
            }
        }, 80);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('ssremoteresponse.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'ssremoteresponse'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/ssremoteresponse');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/ssremoteresponse';
        }, \Config::get('view.paths')), [$sourcePath]), 'ssremoteresponse');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}

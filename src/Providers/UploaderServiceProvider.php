<?php

namespace AhmedAliraqi\LaravelMediaUploader\Providers;

use AhmedAliraqi\LaravelMediaUploader\Console\TemporaryClearCommand;
use AhmedAliraqi\LaravelMediaUploader\Forms\Components\ImageComponent;
use AhmedAliraqi\LaravelMediaUploader\Jobs\PerformConversions;
use AhmedAliraqi\LaravelMediaUploader\Support\FFmpegDriver;
use Elnooronline\LaravelBootstrapForms\Facades\BsForm;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class UploaderServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();

        $this->registerViews();

        $this->publishes([
            __DIR__.'/../Database/Migrations' => database_path('/migrations'),
        ], 'migrations');

        $this->commands([
            TemporaryClearCommand::class,
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('temporary:clean')->everyMinute();
        });

        BsForm::registerComponent('image', ImageComponent::class);

        Config::set([
            'medialibrary.jobs.perform_conversions' => PerformConversions::class,
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton('ffmpeg-driver', function () {
            return (new FFmpegDriver())->driver();
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/laravel-media-uploader.php' => config_path('laravel-media-uploader.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/laravel-media-uploader.php',
            'laravel-media-uploader'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/uploader'),
        ], 'uploader:views');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'uploader');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'uploader');
    }
}

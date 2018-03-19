<?php

namespace Themes\AdminLite\Providers;

use Nova\Package\Support\Providers\ThemeServiceProvider as ServiceProvider;


class ThemeServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the Application Events.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__ .'/../');

        // Configure the Package.
        $this->package('Themes/AdminLite', 'admin_lite', $path);

        // Bootstrap the Theme.
        require $path .DS .'Bootstrap.php';
    }

    /**
     * Register the AdminLite theme Service Provider.
     *
     * This service provider is a convenient place to register your modules
     * services in the IoC container. If you wish, you may make additional
     * methods or service providers to keep the code more focused and granular.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        //
    }

}

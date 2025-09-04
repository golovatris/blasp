<?php

namespace Blaspsoft\Blasp;
 
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Blaspsoft\Blasp\Config\ConfigurationLoader;
use Blaspsoft\Blasp\Contracts\ExpressionGeneratorInterface;
use Blaspsoft\Blasp\Generators\ProfanityExpressionGenerator;
use Blaspsoft\Blasp\Plugins\PluginManager;
use Blaspsoft\Blasp\Registries\LanguageNormalizerRegistry;
use Blaspsoft\Blasp\Registries\DetectionStrategyRegistry;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('blasp.php'),
            ], 'blasp-config');
            
            // Publish language files
            $this->publishes([
                __DIR__.'/../config/languages' => config_path('languages'),
            ], 'blasp-languages');
            
            // Publish both config and languages together
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('blasp.php'),
                __DIR__.'/../config/languages' => config_path('languages'),
            ], 'blasp');
            
            $this->commands([
                Console\Commands\BlaspClearCommand::class,
            ]);
        }

        app('validator')->extend('blasp_check', function($attribute, $value, $parameters, $validator) {
            $language = $parameters[0] ?? null;

            $blaspService = new BlaspService($language);

            return !$blaspService->check($value)->hasProfanity();
        }, 'The :attribute contains profanity.');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'blasp');

        // Register core interfaces and implementations
        $this->app->singleton(ExpressionGeneratorInterface::class, ProfanityExpressionGenerator::class);
        $this->app->singleton(LanguageNormalizerRegistry::class);
        $this->app->singleton(DetectionStrategyRegistry::class);
        
        // Register configuration loader with dependency injection
        $this->app->singleton(ConfigurationLoader::class, function ($app) {
            return new ConfigurationLoader(
                $app->make(ExpressionGeneratorInterface::class)
            );
        });

        // Register plugin manager with dependency injection
        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager(
                $app->make(DetectionStrategyRegistry::class)
            );
        });

        // Register main BlaspService with dependency injection
        $this->app->bind(BlaspService::class, function ($app) {
            return new BlaspService(
                null, // profanities
                null, // false positives
                $app->make(ConfigurationLoader::class)
            );
        });

        // Maintain backward compatibility with 'blasp' alias
        $this->app->bind('blasp', function ($app) {
            return $app->make(BlaspService::class);
        });
    }
}

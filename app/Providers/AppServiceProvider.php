<?php

namespace App\Providers;

use Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Blade::directive('markdown', function ($markdown) {
            if ( ! is_null($markdown)) {
                return "<?php echo Markdown::convertToHtml($markdown); ?>";
            }

            return '<?php ob_start(); ?>';
        });

        Blade::directive('endmarkdown', function () {
            return '<?php echo Markdown::convertToHtml(ob_get_clean()); ?>';
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
    }
}

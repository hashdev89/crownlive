<?php

namespace Webkul\Onepay\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
        
        $this->app->register(OnepayServiceProvider::class);
    }
}


<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    App\Domains\Catalog\Infrastructure\Providers\CatalogServiceProvider::class,
];

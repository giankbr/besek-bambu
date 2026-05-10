<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyResponseProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyResponseProvider::class,
    FortifyServiceProvider::class,
];

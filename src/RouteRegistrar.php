<?php

namespace Modules\Opx\SiteMap;

use Core\Foundation\Module\RouteRegistrar as BaseRouteRegistrar;
use Illuminate\Support\Facades\Route;

class RouteRegistrar extends BaseRouteRegistrar
{
    public function registerPublicRoutes($profile): void
    {
        Route::get('sitemap.xml', 'Modules\Opx\SiteMap\Controllers\SiteMapController@index')
            ->middleware('web');

        Route::get('sitemap/{map}.xml', 'Modules\Opx\SiteMap\Controllers\SiteMapController@map')
            ->middleware('web');
    }
}
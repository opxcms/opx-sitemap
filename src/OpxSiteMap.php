<?php

namespace Modules\Opx\SiteMap;

use Illuminate\Support\Facades\Facade;

/**
 * @method  static string  name()
 * @method  static string  get($key)
 * @method  static string  path($path = '')
 * @method  static array|string|null  config($key = null)
 * @method  static mixed  view($view)
 */
class OpxSiteMap extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'opx_site_map';
    }
}

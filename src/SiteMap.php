<?php

namespace Modules\Opx\SiteMap;

use Core\Foundation\Module\BaseModule;

class SiteMap extends BaseModule
{
    /** @var string  Module name */
    protected $name = 'opx_site_map';

    /** @var string  Module path */
    protected $path = __DIR__;

    /**
     * Override load config.
     */
    protected function loadConfig(): void
    {
        if (file_exists($filename = $this->app->basePath('templates/Opx/SiteMap/config.php'))) {

            $this->config = require $filename;

        } else if (file_exists($filename = "{$this->path}/config.php")) {

            $this->config = require $filename;

        } else {

            $this->config = [];
        }
    }


}

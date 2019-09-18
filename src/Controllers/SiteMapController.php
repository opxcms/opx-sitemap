<?php

namespace Modules\Opx\SiteMap\Controllers;

use Carbon\Carbon;
use Core\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Modules\Opx\SiteMap\OpxSiteMap;
use Illuminate\Http\Response;
use Exception;

class SiteMapController extends Controller
{
    /**
     * Make sitemap index file.
     *
     * @return  Response
     */
    public function index(): Response
    {
        $indexes = [];

        foreach (OpxSiteMap::config() as $name => $class) {
            $last = $this->getLastModifiedModel($class);
            $indexes[] = [
                'loc' => url("/sitemap/{$name}.xml"),
                'lastmod' => $this->getModelTime($last),
            ];
        }

        return response($this->makeIndexXml($indexes), 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Make sitemap for model class.
     *
     * @param string $map
     *
     * @return  Response
     */
    public function map(string $map): Response
    {
        $maps = OpxSiteMap::config();

        if (!isset($maps[$map])) {
            abort(404);
        }

        $class = $maps[$map];

        $indexes = $this->buildSiteMap($class);

        return response($this->makeXml($indexes), 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Get last modified time from class.
     *
     * @param string $class
     *
     * @return  Model|null
     */
    protected function getLastModifiedModel($class): ?Model
    {
        try {
            $model = $class::orderBy('updated_at', 'desc')->first();
        } catch (Exception $e) {
            $model = null;
        }

        return $model;
    }

    /**
     * Make site map index xml.
     *
     * @param array $indexes
     *
     * @return  string
     */
    protected function makeIndexXml($indexes): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($indexes as $index) {
            $xml .= '<sitemap>';
            $xml .= "<loc>{$index['loc']}</loc>";
            $xml .= "<lastmod>{$index['lastmod']}</lastmod>";
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * @param string $class
     *
     * @return  array
     */
    protected function buildSiteMap($class): array
    {
        $models = call_user_func([$class, 'all'], ['id', 'site_map_enable', 'site_map_last_mod_enable', 'site_map_priority', 'site_map_update_frequency', 'published', 'publish_start', 'publish_end']);

        $indexes = [];

        foreach ($models as $model) {
            $index = $this->getSiteMapRecord($model);

            if ($index !== null) {
                $indexes[] = $index;
            }
        }

        return $indexes;
    }

    /**
     * Get record for sitemap.
     *
     * @param Model $model
     *
     * @return  array|null
     */
    protected function getSiteMapRecord(Model $model): ?array
    {
        if (!$model->getAttribute('site_map_enable')
            || (method_exists($model, 'isPublished') && !$model->isPublished())
        ) {
            return null;
        }

        if (!method_exists($model, 'link')) {
            return null;
        }

        return [
            'loc' => url($model->link()),
            'lastmod' => $model->getAttribute('site_map_last_mod_enable') ? $this->carbonToSiteMap($model->getAttribute('updated_at')) : 'disable',
            'priority' => $model->getAttribute('site_map_priority'),
            'changefreq' => $model->getAttribute('site_map_update_frequency'),
        ];
    }

    /**
     * Make models xml.
     *
     * @param array $indexes
     *
     * @return  string
     */
    protected function makeXml($indexes): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($indexes as $index) {
            if (isset($index['loc'])) {
                $xml .= '<url>';
                $xml .= "<loc>{$index['loc']}</loc>";
                if ($index['lastmod'] !== 'disable') {
                    $xml .= "<lastmod>{$index['lastmod']}</lastmod>";
                }
                $xml .= "<priority>{$index['priority']}</priority>";
                $xml .= "<changefreq>{$index['changefreq']}</changefreq>";
                $xml .= '</url>';
            }
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Get timestamp from model.
     *
     * @param Model|null $model
     *
     * @return  string
     */
    protected function getModelTime($model): string
    {
        if ($model === null || ($time = $model->getAttribute('updated_at')) === null) {
            return $this->carbonToSiteMap(Carbon::now());
        }

        return $this->carbonToSiteMap($time);
    }

    /**
     * Format carbon time.
     *
     * @param Carbon|null $time
     *
     * @return  string
     */
    protected function carbonToSiteMap($time): string
    {
        if ($time instanceof Carbon) {
            return $time->tz('GMT')->format('Y-m-d\TH:i:s\+00:00');
        }

        return Carbon::now()->tz('GMT')->format('Y-m-d\TH:i:s\+00:00');
    }
}
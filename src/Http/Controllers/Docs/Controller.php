<?php

declare(strict_types=1);

/*
 * This file is part of the Deployment package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Diviky\Readme\Http\Controllers\Docs;

use App\Http\Controllers\Controller as BaseController;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Controller extends BaseController
{
    public function loadViewsFrom(): string
    {
        return __DIR__;
    }

    public function index($version = null, $page = null): array
    {
        $docs = resolve(Repository::class);
        $versions = $docs->getVersions();

        $config = config('readme');
        $page = $page ?? $config['docs']['landing'];
        $version = $version ?? ($config['versions']['default'] ?? 'master');

        $version = isset($versions[$version]) ? $versions[$version] : $version;

        $content = $docs->getPage($page, $version);
        $title = (new Crawler($content))->filterXPath('//h1');

        try {
            $sections = (new Crawler($content))->filter('.table-of-contents');
        } catch (\Exception $e) {
            $sections = '';
        }

        return [
            'title' => count($title) ? $title->text() : null,
            'sections' => count($sections) ? $sections->outerHtml() : null,
            'content' => $content ?? null,
            'versions' => $versions,
            'version' => $version,
            'route' => $config['docs']['route'],
        ];
    }
}

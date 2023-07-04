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

use Diviky\Readme\Http\Controllers\Docs\Mark\MarkExtension;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Mention\MentionExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Repository
{
    /**
     * The filesystem implementation.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The cache implementation.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Create a new documentation instance.
     */
    public function __construct(Filesystem $files, Cache $cache)
    {
        $this->files = $files;
        $this->cache = $cache;
    }

    /**
     * Get the given documentation page.
     */
    public function getPage(string $page, string $version = '1.0'): string
    {
        $content = $this->getContent($page, $version);
        if ($content) {
            return $this->parse($content);
        }

        return [];
    }

    public function parse(string $content): string
    {
        $config = config('readme.markdown');

        $converter = app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)
            ->commonmarkOptions($config)
            ->addExtension(new GithubFlavoredMarkdownExtension())
            ->addExtension(new AttributesExtension())
            ->addExtension(new FootnoteExtension())
            ->addExtension(new MentionExtension())
            ->addExtension(new SmartPunctExtension())
            ->addExtension(new MarkExtension())
            ->addExtension(new TableExtension())
            ->addExtension(new HeadingPermalinkExtension())
            ->addExtension(new TableOfContentsExtension());

        $extensions = config('readme.extensions');

        if (\is_array($extensions)) {
            foreach ($extensions as $extension) {
                $converter->addExtension($extension);
            }
        }

        return $converter->convertToHtml($content)->getContent();
    }

    /**
     * Replace the version place-holder in links.
     *
     * @return string
     */
    public function replaceLinks(string $content, string $version): ?string
    {
        $config = config('readme');
        $variables = $config['variables'];
        if (!\is_array($variables)) {
            $variables = [];
        }

        $variables['version'] = $version;
        $variables['domain'] = request()->getSchemeAndHttpHost();

        $parsers = $config['parsers'] ?? [];

        if (isset($parsers) && is_array($parsers)) {
            foreach ($parsers as $parser) {
                $content = $this->getClassInstance($parser)->parse($content);
            }
        }

        if (isset($config['blade_support']) && true == $config['blade_support']) {
            $content = $this->blade($content, $variables);
        }

        $content = $this->replaceVariables($content, $variables);

        return $this->parseIncludes($content);
    }

    public function getIndexes($version): ?string
    {
        $documentation = config('readme.docs.menu');

        return $this->getPage($documentation, $version);
    }

    public function getTitle($content): ?string
    {
        $pattern = '/<h[1-2]>([^<]*)<\/h[1-2]/i';

        if (\preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function getVersions(): array
    {
        $versions = config('readme.versions.published');

        if (!\is_array($versions)) {
            return [
                'master' => 'master',
            ];
        }

        $versions = \array_combine($versions, $versions);

        $versions['master'] = \key($versions);

        return $versions;
    }

    protected function replaceVariables($content, array $variables = [])
    {
        foreach ($variables as $key => $value) {
            $value = (string) $value;

            $content = \str_replace('{' . $key . '}', $value, $content);
            $content = \str_replace('{{$' . $key . '}}', $value, $content);
            $content = \str_replace('{{ $' . $key . ' }}', $value, $content);
            $content = \str_replace('{{ ' . $key . ' }}', $value, $content);
            $content = \str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    protected function getClassInstance($class)
    {
        if (is_string($class) && class_exists($class)) {
            $class = new $class();
        }

        return $class;
    }

    protected function parseIncludes(string $content): string
    {
        $re = '/\#include ([\"\'])?([^\"\s\']+)([\"\'])?/m';

        return preg_replace_callback(
            $re,
            function ($matches): ?string {
                if (isset($matches[2])) {
                    return $this->getContent(rtrim($matches[2], '.md'), '');
                }

                return null;
            },
            $content
        );
    }

    protected function getContent(string $page, $version = '1.0')
    {
        $config = config('readme');
        $time = $config['cache_time'] ?? 600;
        $docs = $config['docs'];

        return $this->cache->remember('docs.' . $version . $page, $time, function () use ($version, $page, $docs) {
            $path = $docs['path'] . '/' . $version . '/' . $page;

            if ($this->files->isDirectory($path)) {
                $path .= '/' . $docs['landing'];
            }

            $path .= '.md';
            $path = \str_replace('//', '/', $path);

            if ($this->files->exists($path)) {
                return $this->replaceLinks($this->files->get($path), $version, $path);
            }

            return null;
        });
    }

    /**
     * Render a given blade template with the optionally given data.
     *
     * @param mixed $content
     * @param mixed $data
     */
    protected function blade($content, $data = []): string
    {
        return Blade::render($content, $data);
    }
}

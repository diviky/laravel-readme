<?php

declare(strict_types=1);

/*
 * This file is part of the Readme package.
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

    protected $config = [];
    protected $variables = [];

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
        $config = config('readme');

        $variables = $config['variables'] ?? [];
        if (!\is_array($variables)) {
            $variables = [];
        }

        return $this->setConfig($config)->setVariables($variables)->convert($page, $version);
    }

    public function convert(string $page, string $version = '1.0'): string
    {
        $time = $this->config['cache_time'] ?? 600;

        $content = $this->cache->remember('docs.' . $version . $page, $time, function () use ($version, $page) {
            return $this->toHtml($page, $version);
        });

        $variables = $this->variables;
        $variables['version'] = $version;
        $variables['domain'] = request()->getSchemeAndHttpHost();

        return $this->replaceVariables($content, $variables);
    }

    public function toHtml(string $page, string $version = '1.0'): string
    {
        $content = $this->getMarkdownContent($page, $version);
        if (!empty($content)) {
            return $this->parse($this->replaceLinks($content, $version));
        }

        return '';
    }

    public function parse(string $content): string
    {
        $settings = $this->config['markdown'] ?? [];

        $converter = app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)
            ->commonmarkOptions($settings)
            ->addExtension(new GithubFlavoredMarkdownExtension())
            ->addExtension(new AttributesExtension())
            ->addExtension(new FootnoteExtension())
            ->addExtension(new MentionExtension())
            ->addExtension(new SmartPunctExtension())
            ->addExtension(new MarkExtension())
            ->addExtension(new TableExtension())
            ->addExtension(new HeadingPermalinkExtension())
            ->addExtension(new TableOfContentsExtension());

        $extensions = $this->config['extensions'] ?? [];

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
        $variables = $this->variables;
        if (!empty($version)) {
            $variables['version'] = $version;
        }

        $content = $this->replaceVariables($content, $variables);

        $parsers = $this->config['parsers'] ?? [];
        $parsers = array_merge($this->config['default_parsers'] ?? [], $parsers);

        if (isset($parsers) && is_array($parsers)) {
            foreach ($parsers as $parser) {
                $content = $this->getClassInstance($parser)->parse($content, $variables, $this->config);
            }
        }

        if (isset($this->config['blade_support']) && true == $this->config['blade_support']) {
            $content = $this->blade($content, $variables);
        }

        return $this->parseIncludes($content);
    }

    public function getIndexes($version): ?string
    {
        $documentation = config('readme.docs.menu', 'documentation');

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

    /**
     * Get the value of config.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set the value of config.
     *
     * @param array $config
     *
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the value of variables.
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Set the value of variables.
     *
     * @param mixed $variables
     *
     * @return self
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;

        return $this;
    }

    protected function replaceVariables(string $content, array $variables = []): string
    {
        foreach ($variables as $key => $value) {
            $value = (string) $value;

            $content = \str_replace('##' . $key . '##', $value, $content);
            $content = \str_replace('{' . $key . '}', $value, $content);
            $content = \str_replace('{{$' . $key . '}}', $value, $content);
            $content = \str_replace('{{ $' . $key . ' }}', $value, $content);
            $content = \str_replace('{{ ' . $key . ' }}', $value, $content);
            $content = \str_replace('{{' . $key . '}}', $value, $content);
            $content = preg_replace('/\{[^\}]+(' . $key . ')[^\}]+\}/m', $value, $content);
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
        $regx = '/\#include ([\"\'])?([^\"\s\']+)([\"\'])?/m';

        if (!preg_match($regx, $content)) {
            return $content;
        }

        return preg_replace_callback(
            $regx,
            function ($matches): ?string {
                if (isset($matches[2])) {
                    return $this->toHtml($matches[2], '');
                }

                return '';
            },
            $content
        );
    }

    protected function getMarkdownContent(string $page, string $version = '1.0'): string
    {
        $docs = $this->config['docs'];
        $path = $docs['path'] . '/' . $version . '/' . $page;

        if ($this->files->isDirectory($path)) {
            $path .= '/' . $docs['landing'];
        }

        $path = rtrim($path, '.md') . '.md';
        $path = \str_replace('//', '/', $path);

        if ($this->files->exists($path)) {
            return $this->files->get($path);
        }

        return '';
    }

    /**
     * Render a given blade template with the optionally given data.
     *
     * @param string $content
     * @param array  $data
     */
    protected function blade($content, $data = []): string
    {
        return Blade::render($content, $data);
    }
}

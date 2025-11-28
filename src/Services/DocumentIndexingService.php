<?php

declare(strict_types=1);

namespace Diviky\Readme\Services;

use Diviky\Readme\Http\Controllers\Docs\Repository;
use Diviky\Readme\Models\Document;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class DocumentIndexingService
{
    protected Filesystem $files;

    protected Repository $repository;

    public function __construct(Filesystem $files, Repository $repository)
    {
        $this->files = $files;
        $this->repository = $repository;
    }

    public function indexAllDocuments(?string $version = null, bool $force = false): int
    {
        $count = 0;
        $versions = $version ? [$version] : $this->getVersions();

        foreach ($versions as $versionKey) {
            $markdownFiles = $this->getMarkdownFiles($versionKey);

            foreach ($markdownFiles as $file) {
                $page = $this->getPageFromPath($file, $versionKey);

                if ($this->indexDocument($versionKey, $page, $force)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function indexDocument(string $version, string $page, bool $force = false): bool
    {
        $config = config('readme');
        $docsPath = $config['docs']['path'] ?? base_path('vendor/diviky/docs');
        $filePath = $this->getFilePath($docsPath, $version, $page);

        if (!$this->files->exists($filePath)) {
            return false;
        }

        $content = $this->files->get($filePath);
        $fileHash = md5($content);

        $document = Document::where('version', $version)
            ->where('page', $page)
            ->first();

        if ($document && !$force) {
            if ($document->file_hash === $fileHash) {
                return false;
            }
        }

        $htmlContent = $this->repository->setConfig($config)->getPageHtml($page, $version);
        $title = $this->extractTitle($content);

        $data = [
            'version' => $version,
            'page' => $page,
            'title' => $title,
            'content' => $content,
            'html_content' => $htmlContent,
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'indexed_at' => now(),
        ];

        if ($document) {
            $document->update($data);
            $document->searchable();
        } else {
            $document = Document::create($data);
            $document->searchable();
        }

        return true;
    }

    public function updateDocumentIfChanged(Document $document): bool
    {
        if (!$this->files->exists($document->file_path)) {
            $document->unsearchable();
            $document->delete();

            return false;
        }

        $content = $this->files->get($document->file_path);
        $fileHash = md5($content);

        if ($document->file_hash === $fileHash) {
            return false;
        }

        $config = config('readme');
        $htmlContent = $this->repository->setConfig($config)->getPageHtml($document->page, $document->version);
        $title = $this->extractTitle($content);

        $document->update([
            'title' => $title,
            'content' => $content,
            'html_content' => $htmlContent,
            'file_hash' => $fileHash,
            'indexed_at' => now(),
        ]);

        $document->searchable();

        return true;
    }

    protected function getMarkdownFiles(string $version): array
    {
        $config = config('readme');
        $docsPath = $config['docs']['path'] ?? base_path('vendor/diviky/docs');
        $versionPath = $docsPath . '/' . $version;

        if (!$this->files->exists($versionPath)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($versionPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    protected function getPageFromPath(string $filePath, string $version): string
    {
        $config = config('readme');
        $docsPath = $config['docs']['path'] ?? base_path('vendor/diviky/docs');
        $versionPath = $docsPath . '/' . $version;

        $relativePath = Str::after($filePath, $versionPath . '/');
        $page = Str::before($relativePath, '.md');

        return $page;
    }

    protected function getFilePath(string $docsPath, string $version, string $page): string
    {
        $path = $docsPath . '/' . $version . '/' . $page;

        if ($this->files->isDirectory($path)) {
            $config = config('readme');
            $landing = $config['docs']['landing'] ?? 'index';
            $path .= '/' . $landing;
        }

        $path = rtrim($path, '.md') . '.md';

        return str_replace('//', '/', $path);
    }

    protected function extractTitle(string $content): ?string
    {
        $pattern = '/^#\s+(.+)$/m';

        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }

        $pattern = '/^##\s+(.+)$/m';

        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function getVersions(): array
    {
        $versions = config('readme.versions.published', []);

        if (!is_array($versions) || empty($versions)) {
            return ['master'];
        }

        return $versions;
    }
}


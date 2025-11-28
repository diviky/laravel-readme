<?php

declare(strict_types=1);

namespace Diviky\Readme\Livewire\Docs;

use Diviky\Readme\Models\Document;
use Illuminate\Support\Str;
use Livewire\Component;

class Search extends Component
{
    public string $query = '';

    public ?string $version = null;

    public array $results = [];

    public bool $showResults = false;

    protected $listeners = ['versionChanged' => 'updateVersion'];

    public function mount(?string $version = null): void
    {
        $this->version = $version;
    }

    public function updatedQuery(): void
    {
        $this->performSearch();
    }

    public function performSearch(): void
    {
        if (empty(trim($this->query))) {
            $this->results = [];
            $this->showResults = false;

            return;
        }

        try {
            $searchQuery = Document::search($this->query);

            if ($this->version) {
                $searchQuery->where('version', $this->version);
            }

            $documents = $searchQuery->take(10)->get();

            $this->results = $documents->map(function ($document) {
                $content = $document->content ?? '';
                $excerpt = !empty($content) ? Str::limit(strip_tags($content), 150) : '';

                return [
                    'id' => $document->id ?? null,
                    'version' => $document->version ?? '',
                    'page' => $document->page ?? '',
                    'title' => $document->title ?? $document->page ?? '',
                    'excerpt' => $excerpt,
                ];
            })->toArray();

            $this->showResults = !empty($this->results);
        } catch (\Exception $e) {
            $this->results = [];
            $this->showResults = false;
        }
    }

    public function updateVersion(?string $version): void
    {
        $this->version = $version;

        if (!empty($this->query)) {
            $this->performSearch();
        }
    }

    public function render()
    {
        return view('readme::livewire.docs.search');
    }
}

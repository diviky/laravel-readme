<div class="readme-search-container" x-data="{ open: @entangle('showResults') }" @click.away="open = false">
    <div class="readme-search-input-wrapper">
        <div class="input-icon">
            <input type="text" wire:model.live.debounce.300ms="query" wire:keydown.enter="performSearch"
                placeholder="Search documentation..." class="form-control form-control-rounded"
                x-on:focus="open = true" />
            <span class="input-icon-addon">
                <i class="ti ti-search"></i>
            </span>
        </div>
    </div>

    <div x-show="open && @entangle('showResults')" x-transition class="readme-search-results" wire:loading.class="loading">
        <div wire:loading class="readme-search-loading">
            Searching...
        </div>

        <div wire:loading.remove>
            @if (empty($results))
                @if (!empty($query))
                    <div class="readme-search-empty">
                        No results found for "{{ $query }}"
                    </div>
                @endif
            @else
                <div class="readme-search-results-list">
                    @foreach ($results as $index => $result)
                        <a href="{{ url(config('readme.docs.route', '/docs') . '/' . ($result['version'] ?? '') . '/' . ($result['page'] ?? '')) }}"
                            class="readme-search-result-item" wire:key="result-{{ $result['id'] ?? $index }}">
                            <div class="readme-search-result-title">
                                {{ $result['title'] ?? $result['page'] ?? 'Untitled' }}
                            </div>
                            <div class="readme-search-result-meta">
                                <span class="readme-search-result-version">{{ $result['version'] ?? '' }}</span>
                                <span class="readme-search-result-page">{{ $result['page'] ?? '' }}</span>
                            </div>
                            @if (!empty($result['excerpt']))
                                <div class="readme-search-result-excerpt">
                                    {{ $result['excerpt'] }}
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

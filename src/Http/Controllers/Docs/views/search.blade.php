<div class="doc-hub-outer-container">
    <div class="docs-hub">
        <div class="container-fluid">
            <div class="row">
                <div class="col-8">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('docs/') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ url('docs/' . ($version ?? 'master')) }}">{{ $version ?? 'master' }}</a>
                        </li>
                        <li class="breadcrumb-item">Search Results</li>
                    </ol>
                </div>
                <div class="col-4">
                    @livewire(\Diviky\Readme\Livewire\Docs\Search::class, ['version' => $version ?? null])
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row doc-hub-content-container">
            <div class="col-3 doc-hub-sidebar" id="header-menu">
                <div class="doc-hub-container">
                    <x-readme::indexes :version="$version ?? 'master'" />
                </div>
            </div>
            <div class="col-9 doc-hub-page">
                <div id="docs-hub-main-container" data-pjax-container>
                    @fragment('content')
                        <div class="row">
                            <div class="col-12">
                                <div class="doc-hub-body">
                                    <h1>Search Results</h1>
                                    @if(!empty($query))
                                        <p>Found {{ $results->total() ?? 0 }} results for "<strong>{{ $query }}</strong>"</p>

                                        @if($results->count() > 0)
                                            <div class="readme-search-results-page">
                                                @foreach($results as $result)
                                                    <div class="readme-search-result-card">
                                                        <h3>
                                                            <a href="{{ url($route . '/' . $result->version . '/' . $result->page) }}">
                                                                {{ $result->title ?? $result->page }}
                                                            </a>
                                                        </h3>
                                                        <div class="readme-search-result-meta">
                                                            <span class="readme-search-result-version">{{ $result->version }}</span>
                                                            <span class="readme-search-result-page">{{ $result->page }}</span>
                                                        </div>
                                                        @if(!empty($result->content))
                                                            <p class="readme-search-result-excerpt">
                                                                {{ Str::limit(strip_tags($result->content), 300) }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endforeach

                                                <div class="readme-search-pagination">
                                                    {{ $results->links() }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="readme-search-empty-page">
                                                <p>No results found. Try different keywords or check your spelling.</p>
                                            </div>
                                        @endif
                                    @else
                                        <p>Please enter a search query.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endfragment
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .readme-search-result-card {
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: white;
    }

    .readme-search-result-card h3 {
        margin-top: 0;
        margin-bottom: 8px;
    }

    .readme-search-result-card h3 a {
        color: #3b82f6;
        text-decoration: none;
    }

    .readme-search-result-card h3 a:hover {
        text-decoration: underline;
    }

    .readme-search-result-meta {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 12px;
        color: #6b7280;
    }

    .readme-search-result-version {
        background-color: #e5e7eb;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .readme-search-result-excerpt {
        color: #6b7280;
        line-height: 1.6;
    }

    .readme-search-empty-page {
        padding: 40px;
        text-align: center;
        color: #6b7280;
    }

    .readme-search-pagination {
        margin-top: 30px;
    }
</style>


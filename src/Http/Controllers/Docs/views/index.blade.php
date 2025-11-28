<div class="doc-hub-outer-container">
    <div class="docs-hub p-2">
        <div class="row">
            <div class="col-8">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('docs/') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('docs/' . $version) }}">{{ $version }}</a>
                    </li>
                    <li class="breadcrumb-item" id="breadcrumb-title">{{ $title }}</li>
                </ol>
            </div>
            <div class="col-4">
                <livewire:readme.docs.search :version="$version" />
            </div>
        </div>
    </div>
    <div class="row doc-hub-content-container">
        <div class="col-3 doc-hub-sidebar" id="header-menu">
            <div class="doc-hub-container">
                <x-readme::indexes :version="$version" />
            </div>
        </div>
        <div class="col-9 doc-hub-page">
            <div id="docs-hub-main-container" data-pjax-container>
                @fragment('content')
                    <div class="row">
                        <div class="col-8">
                            <div class="doc-hub-body">
                                <title>{{ $title }}</title>
                                {!! $content !!}
                            </div>
                        </div>
                        <div class="col-4 doc-hub-sections" data-sticky="100">
                            <div class="doc-hub-container">
                                <div class="doc-hub-sections-items">
                                    <div class="doc-hub-sections-title">
                                        <a href="#"><i class="ti ti-menu-2"></i> Table of Contents</a>
                                    </div>
                                    {!! $sections !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endfragment
            </div>
        </div>
    </div>
</div>

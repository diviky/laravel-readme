@view('pjax')
<div class="doc-hub-outer-container">
    <div class="docs-hub">
        <div class="container">
            <div class="row">
                <div class="col-1">
                    <a href="/">
                        <img src="{{ asset('/assets/images/logo-small.png') }}" width="40">
                    </a>
                </div>
                <div class="col-8">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">{{ $title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row doc-hub-content-container">
            <div class="col doc-hub-sidebar" data-sticky="100" data-pjax>
                <div class="doc-hub-container">
                    <div class="doc-hub-items" id="doc-hub-indexes">{!! $index !!}</div>
                </div>
            </div>
            <div class="col doc-hub-page" data-pjax-container>
                @endview
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
                                    <a href="#">
                                        <i class="fa fa-align-left"></i> Table of Contents</a>
                                </div>
                                <ul role="page-nav">
                                    @foreach ($sections as $item)
                                    <li>
                                        <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                                        @if (isset($item['childs']))
                                        <ul>
                                            @foreach ($item['childs'] as $child)
                                            <li>
                                                <a href="{{ url($child['url']) }}">{{ $child['name'] }}</a>
                                                @if (isset($child['childs']))
                                                <ul>
                                                    @foreach ($child['childs'] as $gchild)
                                                    <li>
                                                        <a href="{{ $gchild['url'] }}">{{ $gchild['name'] }}</a>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @view('pjax')
            </div>
        </div>
    </div>
</div>
@endview

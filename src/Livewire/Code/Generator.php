<?php

namespace Diviky\Readme\Livewire\Code;

use Diviky\Readme\Http\Controllers\Docs\Repository;
use Diviky\Readme\Parsers\CodeParser;
use Livewire\Component;

class Generator extends Component
{
    public string $content = '';

    public string $file;

    public function mount(string $file)
    {
        $this->file = $file;

        $this->content = $this->parseCode($file);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            loading...
        </div>
        HTML;
    }

    public function render()
    {
        return view('readme::livewire.code.generator');
    }

    protected function parseCode(string $file): string
    {
        $parser = new CodeParser;
        $parser->setConfig(config('readme'));

        $languages = $parser->getAvailableLanguages() ?? [];

        if (empty($languages)) {
            return '';
        }

        $repo = app(Repository::class);
        $repo->setConfig(config('readme'));

        return $repo->toHtml($parser->snippets($file, $languages));
    }
}

<?php

namespace Diviky\Readme\Component;

use Diviky\Readme\Http\Controllers\Docs\Repository;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class Indexes extends Component
{
    public $version;

    public function __construct(?string $version = null)
    {
        $this->version = $version;
    }

    public function render()
    {
        $docs = resolve(Repository::class);
        $version = $this->version ?? ($docs->getConfig()['versions']['default'] ?? 'master');

        $indexes = $docs->getIndexes($version);

        return View::make('readme::components.indexes', [
            'indexes' => $indexes,
        ]);
    }
}

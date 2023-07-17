<?php

declare(strict_types=1);

Route::get('/{version?}/{slug?}', 'Docs\Controller@index')->name('docs.index')->where('slug', '.*');

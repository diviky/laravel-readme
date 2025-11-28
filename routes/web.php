<?php

declare(strict_types=1);

Route::get('/search', 'Docs\Controller@search')->name('docs.search');
Route::get('/{version?}/{slug?}', 'Docs\Controller@index')->name('docs.index')->where('slug', '.*');

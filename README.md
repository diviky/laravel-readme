# An extension to documentation generator

```php
    php artisan vendor:publish --provider="Diviky\Readme\ReadmeServiceProvider" --tag="config"
```

Add to your route config

```php
Route::group(['middleware' => ['web']], function () {
    Route::get('docs/{version?}/{page?}', '\Diviky\Readme\Http\Controllers\Docs\Controller@index');
});
```

### Replace variables

You can use the Laravel blade syntax inside markdown files to replace the variables and render conditions.

Variables wrapped between `##` will be replaced with their corresponding values. ex: `##version##` or `##domain##`


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

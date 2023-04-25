# Rapidez Statamic

This package helps you integrate Statamic within your Rapidez project by adding some good starting points:

- Products, categories and brands are integrated through [Runway](https://github.com/duncanmcclean/runway) as read only resources so you can link them to the content
- Products, categories and pages collections as starting point so you can extend this however you'd like
- Route merging so Statamic routes work as fallback
- Page builder fieldset with a productlist and text component
- Breadcrumbs for pages
- Globals available in all views

## Requirements

You need to have `statamic/cms` installed in your Rapidez installation. Follow the [installation guide](https://statamic.dev/installing/laravel).

## Installation

```
composer require rapidez/statamic
```

## Configuration

Have a look within the `rapidez-statamic.php` config file, if you need to change something you can publish it with:

```
php artisan vendor:publish --provider="Rapidez\Statamic\RapidezStatamicServiceProvider" --tag=config
```

### Routing

As Rapidez uses route fallbacks to allow routes to be added with lower priority then Magento routes, this package is used to fix this, as statamic routes on itself will overwrite your Magento routes. Make sure default Statamic routing is disabled in `config/statamic/routes.php`. We'll register the Statamic routes from this packages after the Magento routes.

```php
'enabled' => false,
```

### Publish Collections & Blueprints

```
php artisan vendor:publish --provider="Rapidez\Statamic\RapidezStatamicServiceProvider" --tag=rapidez-collections
```

### Magento Store ID

It is important to add the Magento store ID in the attributes section within `config/statamic/sites.php`

```php
'sites' => [
    'default' => [
        'name' => config('app.name'),
        'locale' => 'nl_NL',
        'url' => '/',
        'attributes' => [
            'magento_store_id' => 1,
        ]
    ],
]
```

## License

GNU General Public License v3. Please see [License File](LICENSE) for more information.

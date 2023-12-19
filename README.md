# Rapidez Statamic

This package helps you integrate Statamic within your Rapidez project by adding some good starting points:

- Products, categories and brands are integrated through [Runway](https://github.com/duncanmcclean/runway) as read only resources so you can link them to the content
- Products, categories, brands and pages collections as starting point so you can extend this however you'd like
- Route merging so Statamic routes work as fallback
- Page builder fieldset with a product slider, content, image and form component
- Responsive images with [spatie/statamic-responsive-images](https://github.com/spatie/statamic-responsive-images)
- Breadcrumbs for pages
- Globals available in all views
- Meta title, description and automatic alternate hreflang link tags

## Requirements

You need to have `statamic/cms` installed in your Rapidez installation. Follow the [installation guide](https://statamic.dev/installing/laravel).

## Installation

```
composer require rapidez/statamic
```

## Configuration

Have a look within the `rapidez/statamic.php` config file, if you need to change something you can publish it with:

```
php artisan vendor:publish --provider="Rapidez\Statamic\RapidezStatamicServiceProvider" --tag=config
```

### Assets disk

Make sure there is an assets disk within `config/filesystems.php`
```
'disks' => [
    'assets' => [
        'driver' => 'local',
        'root' => public_path('assets'),
        'url' => '/assets',
        'visibility' => 'public',
    ],
],
```

### Routing

As Rapidez uses route fallbacks to allow routes to be added with lower priority than Magento routes, this package is used to fix this, as statamic routes on itself will overwrite your Magento routes. Make sure default Statamic routing is disabled in `config/statamic/routes.php`. We'll register the Statamic routes from this packages after the Magento routes.

```php
'enabled' => false,
```

#### Homepage

If you'd like to use the homepage from Statamic instead of the CMS page from Magento; just disable the homepage in Magento.

### Publish Collections, Blueprints and Fieldsets

```
php artisan vendor:publish --provider="Rapidez\Statamic\RapidezStatamicServiceProvider" --tag=rapidez-statamic-content
```

And if you'd like to change the views:

```
php artisan vendor:publish --provider="Rapidez\Statamic\RapidezStatamicServiceProvider" --tag=views
```


### Magento Store ID

It is important to add the Magento store ID for every site in the attributes section within `config/statamic/sites.php` and use the store code as key. The current site will be determined based on the `MAGE_RUN_CODE`. By default Statamic uses the url for this; that's still the fallback. If you need to generate some urls with a multisite it's a best practice to specify the `url` per site from env variables. See the [Statamic multisite docs](https://statamic.dev/multi-site#url). Optionally you could set the `group` within the `attributes` if you'd like to group sites to filter the alternate hreflang link tags. You could also set the `disabled` within the `attributes` section to true if you want to exclude this site from being altered with Statamic data.

```php
'sites' => [
    'default' => [
        'name' => config('app.name'),
        'locale' => 'nl_NL',
        'url' => '/',
        'attributes' => [
            'magento_store_id' => 1,
            'group' => 'default',
            'disabled' => false,
        ]
    ],
]
```

### Showing content on categories and products

By default you'll get the configured content on categories and products available withint the `$content` variable. This can be enabled/disabled with the `fetch` configurations within the `rapidez/statamic.php` config file. If you want to display the configured content from the default page builder you can include this in your view:
```
@includeWhen(isset($content), 'rapidez-statamic::page_builder', ['content' => $content?->content])
```
- Product: `resources/views/vendor/rapidez/product/overview.blade.php`
- Category: `resources/views/vendor/rapidez/category/overview.blade.php`

### Importing categories or products from Magento

#### Categories

To make it easier to change category content in bulk you can create category entries with content copied over in bulk.

To do this run one of the following:

```bash
# Most basic, import all categories in all sites
php artisan rapidez:statamic:import:categories --all

# Import all categories in the site with handle "default" only
php artisan rapidez:statamic:import:categories --all --site=default

# import select categories in multiple sites
php artisan rapidez:statamic:import:categories 5 8 9 category-url-key --site=default --site=another_site
```

By default the slug and title of the category are copied.

If you have a custom blueprint and would like to add more data from the category you can do so by hooking into the `rapidez-statamic:category-entry-data` event:

```php
Event::listen('rapidez-statamic:category-entry-data', fn($category) => [
        'description' => $category->description,
    ]
);
```

#### Products

To make it easier to change product content in bulk you can create product entries with content copied over in bulk.

To do this run one of the following:

```bash
# Most basic, import all products in all sites
php artisan rapidez:statamic:import:products

# Import all products in the site with handle "default" only
php artisan rapidez:statamic:import:products --site=default
```

By default the slug and title of the product are copied.

If you have a custom blueprint and would like to add more data from the product you can do so by hooking into the `rapidez-statamic:product-entry-data` event:

```php
Event::listen('rapidez-statamic:product-entry-data', fn($product) => [
        'description' => $product->description,
    ]
);
```

#### Brands

To make it easier to change brands content in bulk you can create brand entries with content copied over in bulk.

To do this run one of the following:

```bash
# Import all brands in all sites
php artisan rapidez:statamic:import:brands

# Import all brands in the site with handle "default" only
php artisan rapidez:statamic:import:brands --site=default
```

By default the slug and title of the brand are copied.

If you have a custom blueprint and would like to add more data from the brand you can do so by hooking into the `rapidez-statamic:brand-entry-data` event:

```php
Event::listen('rapidez-statamic:brand-entry-data', fn($brand) => [
        'description' => $brand->description,
    ]
);
```

### Forms

When you create a form you could use `rapidez-statamic::emails.form` as HTML template which uses the [Laravel mail template](https://laravel.com/docs/master/mail#customizing-the-components) with all fields in a table, make sure you enable markdown!

## License

GNU General Public License v3. Please see [License File](LICENSE) for more information.

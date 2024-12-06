# Rapidez Statamic

This package helps you integrate Statamic within your Rapidez project by adding some good starting points:

- Products, categories and brands are integrated through [Runway](https://github.com/duncanmcclean/runway) as read only resources so you can link them to the content
- Products, categories, brands and pages collections as starting point so you can extend this however you'd like
- Route merging so Statamic routes work as fallback
- Page builder fieldset with a product slider, content, image and form component
- Responsive images with Glide through [justbetter/statamic-glide-directive](https://github.com/justbetter/statamic-glide-directive)
- Breadcrumbs for pages
- Globals available in all views
- Meta title, description and automatic alternate hreflang link tags

## Requirements

You need to have `statamic/cms` installed in your Rapidez installation. Follow the [installation guide](https://statamic.dev/installing/laravel).

## Installation

```
composer require rapidez/statamic
```

## Install command

The install command will help you set up all the necessary settings.
It will mainly setup the [Eloquent driver](https://github.com/statamic/eloquent-driver) and publish the necessary vendor from the rapidez/statamic repo. 

```
php artisan rapidez-statamic:install
```

When running the install command you will be prompted to setup the Eloquent driver.
In this configuration you can choose what to keep in the flat file system and what to migrate to the database.
We recommend migrating the following options to the database when setting up the eloquent driver:
- Assets
- Collection Trees
- Entries
- Forms
- Form Submissions
- Globals
- Global Variables
- Navigation Trees
- Terms
- Tokens

After you're done running the install command make sure to check the guide below for some manual changes.

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

If you have run the install command these will already have been published.

```
php artisan vendor:publish --provider="Rapidez\Statamic\RapidezStatamicServiceProvider" --tag=rapidez-statamic-content
```

And if you'd like to change the views:

```
php artisan vendor:publish --provider="Rapidez\Statamic\RapidezStatamicServiceProvider" --tag=views
```


### Magento Store ID

It is important to add the Magento store ID for every site in the attributes section within `resources/sites.yaml` and use the store code as key. Because the url can vary per enviroment(local, testing, staging, production) we use the "sites" section of the config file and reference that in the `sites.yaml`. The current site will be determined based on the `MAGE_RUN_CODE`. By default Statamic uses the url for this; that's still the fallback. If you need to generate some urls with a multisite it's a best practice to specify the `url` per site from env variables. See the [Statamic multisite docs](https://statamic.dev/multi-site#url). Optionally you could set the `group` within the `attributes` if you'd like to group sites to filter the alternate hreflang link tags. You could also set the `disabled` within the `attributes` section to true if you want to exclude this site from being altered with Statamic data.
```yaml
'default' => [
    'name' => env('APP_NAME', 'Statamic'),
    'locale' => 'en_EN',
    'lang' => 'en_EN',
    'url' => '/',
    'attributes' => [
        'magento_store_id' => 1,
        'group' => 'default',
        'disabled' => false,
    ],
],
```


```yaml
default:
  name: '{{ config:rapidez.statamic.sites.default.name }}'
  locale: '{{ config:rapidez.statamic.sites.default.locale }}'
  lang: '{{ config:rapidez.statamic.sites.default.lang }}'
  url: '{{ config:rapidez.statamic.sites.default.url }}'
  attributes:
    magento_store_id: '{{ config:rapidez.statamic.sites.default.attributes.magento_store_id }}'
    group: '{{ config:rapidez.statamic.sites.default.attributes.group }}'
    disabled: '{{ config:rapidez.statamic.sites.default.attributes.disabled }}'
```

### Showing content on categories and products

By default you'll get the configured content on categories and products available withint the `$content` variable. This can be enabled/disabled with the `fetch` configurations within the `rapidez/statamic.php` config file. If you want to display the configured content from the default page builder you can include this in your view:
```
@includeWhen(isset($content), 'rapidez-statamic::page_builder', ['content' => $content?->content])
```
- Product: `resources/views/vendor/rapidez/product/overview.blade.php`
- Category: `resources/views/vendor/rapidez/category/overview.blade.php`


### Brand overview and single brand
#### Brand pages
Single brand pages display a listing page with your products linked to that brand. By default the single brand pages are disabled. You can enable routing for them in `content/collections/brands.yaml` by adding a route for them:

```yaml
    route: '/brands/{slug}'
```
#### Brand overview
If you want an overview page for your brands you can add a `Brand overview` component on a normal page. This will automaticly load a view with your brands grouped by their first letter.

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

If you have a custom blueprint and would like to add more data from the category you can do so by hooking into the Eventy event: `rapidez-statamic:category-entry-data`

```php
Eventy::addFilter('rapidez.statamic.category.entry.data', fn($category) => [
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

If you have a custom blueprint and would like to add more data from the product you can do so by hooking into the Eventy event: `rapidez.statamic.product.entry.data`

```php
Eventy::addFilter('rapidez.statamic.product.entry.data', fn($product) => [
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

If you have a custom blueprint and would like to add more data from the brand you can do so by hooking into the Eventy event `rapidez.statamic.brand.entry.data`

```php
Eventy::addFilter('rapidez.statamic.brand.entry.data', fn($brand) => [
        'description' => $brand->description,
    ]
);
```


### Globals

Globals will be available through the `$globals` variable.
For example; If you created a global with the handle `header` and added a field called `logo` in this global it will be available as `$globals->header->logo`.


### Forms

When you create a form you could use `rapidez-statamic::emails.form` as HTML template which uses the [Laravel mail template](https://laravel.com/docs/master/mail#customizing-the-components) with all fields in a table, make sure you enable markdown!

### Sitemap

We hook into the [Rapidez Sitemap](https://github.com/rapidez/sitemap) generation by adding our Statamic-specific sitemaps to the store's sitemap index. 

For each store, we generate sitemaps for collections and taxonomies that have actual routes and content. The XML files will be stored in the configured sitemap disk (defaults to 'public') under the configured path (defaults to 'rapidez-sitemaps') with the following structure:
```shell
rapidez-sitemaps/{store_id}/{sitemap_prefix}_collection_{collection_handle}.xml
rapidez-sitemaps/{store_id}/{sitemap_prefix}_taxonomy_{taxonomy_handle}.xml
```

The sitemap prefix can be configured in the `rapidez.statamic.sitemap.prefix` config.

### Upgrading

Since 3.0.0 we have started using optionalDeep for the $globals, and $content variables.
This means some code may need to be upgraded. Here's a list of things you can expect might need to be changed:

Since optionalDeep will always return the optional class we explicitly need to ask it if a value is set
```diff
- @if($globals->cookie_notice->text)
+ @if($globals->cookie_notice->text->isset())
```

This also means that checks for specific values need to be updated
```diff
- @if($globals->cookie_notice->text === 'foo')
+ @if($globals->cookie_notice->text->get() === 'foo')
```

However, anything that will attempt to cast the value to string will get the value right away. Thus bits that can stay unchanged are:
```blade
{{ $globals->cookie_notice->text }}
{{ $globals->cookie_notice->text ?? 'fallback value' }}
{{ $globals->cookie_notice->text . ' string concatenation' }}
@foreach($content->blocks as $contentBlock)

@endForeach
```

## License

GNU General Public License v3. Please see [License File](LICENSE) for more information.

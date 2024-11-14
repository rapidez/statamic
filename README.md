# Rapidez Statamic

This package helps you integrate Statamic within your Rapidez project by adding some good starting points:

- Products, categories and brands are integrated through [Runway](https://github.com/duncanmcclean/runway) as read only resources so you can link them to the content
- Products, categories, brands and pages collections as starting point so you can extend this however you'd like
- Site registration based on the active Magento stores
- Route merging so Statamic routes work as fallback
- Page builder fieldset with a product slider, content, image and form component
- Responsive images with Glide through [justbetter/statamic-glide-directive](https://github.com/justbetter/statamic-glide-directive)
- Breadcrumbs for pages
- Globals available in all views
- Meta title, description and automatic alternate hreflang link tags

## Requirements

You need to have `statamic/cms` installed in your Rapidez installation. Follow the [installation guide](https://statamic.dev/installing/laravel).

Make sure you have an existing User model as Statamic requires this.
If you don't have a User model you can get it from the [Laravel repo](https://github.com/laravel/laravel/blob/11.x/app/Models/User.php).

You will also need the migrations for the users table, this can be found [here](https://github.com/laravel/laravel/blob/11.x/database/migrations/0001_01_01_000000_create_users_table.php).
At last you need to make some changes to your User model to make it compatible with Statamic, 
make sure to follow [this guide](https://statamic.dev/tips/storing-users-in-a-database#in-an-existing-laravel-app) after requiring `rapidez/statamic` in your project.

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

After you're done running the install command make sure to check our configuration guide written [here](https://docs.rapidez.io/3.x/packages/statamic.html).

### Sitemap

We hook into the [Rapidez Sitemap](https://github.com/rapidez/sitemap) generation action by triggering our own sitemap generation. 

This creates multiple sitemaps based on the store ID for each collection or taxonomy that has an actual route. The XML files will be generated as:
```shell
/statamic_sitemap_collection_{store_id}_collection_{collection_handle}.xml
/statamic_sitemap_taxonomy_{store_id}_taxonomy_{taxonomy_handle}.xml
```

You can also generate the sitemaps manually by running:
```shell
php artisan rapidez:statamic:generate:sitemap
```

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

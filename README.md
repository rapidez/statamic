# Rapidez Statamic

## Requirements

You have to have `statamic/cms` installed in your laravel application. Installation guide is found [here](https://statamic.dev/installing/laravel).

## Installation

```
composer require rapidez/statamic
```

### Routing

As Rapidez uses route fallbacks to allow routes to be added with lower priority then Magento routes, this package is used to fix this, as statamic routes on itself will overwrite your Magento routes. Make sure default Statamic routing is disabled in `config/statamic/routes.php`:

```php

'enabled' => false,

```

### Commands

There are 2 commands that trigger 2 seperate jobs. One is for importing the stores from Magento and the other is for importing the products from Magento.

#### Store import
```
php artisan rapidez:statamic:sync:stores
```

#### Product import
```
php artisan rapidez:statamic:sync:products
```

### Publish

```
php artisan vendor:publish --provider="Rapidez\Statamic\RapidezStatamicServiceProvider"
```

## License

GNU General Public License v3. Please see [License File](LICENSE) for more information.
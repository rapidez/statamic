# Rapidez Statamic

## Requirements

You have to have `statamic/cms` installed in your laravel application. Installation guide is found [here](https://statamic.dev/installing/laravel).

## Installation

```
composer require rapidez/statamic
```

### Routing

As Rapidez uses route fallbacks to allow routes to be added with lower priority then Magento routes, this package is used to fix this, as statamic routes on itself will overwrite your Magento routes. Make sure default Statamic routing is disabled in `config/statamic/routes.php`.

```php

	'enabled' => false,

```

## License

GNU General Public License v3. Please see [License File](LICENSE) for more information.
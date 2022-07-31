# Rapidez Statamic

## Requirements

You have to have `statamic/cms` installed in your laravel application. Installation guide is found [here](https://statamic.dev/installing/laravel).

## Installation

```
composer require rapidez/statamic
```

## Configuration

You can publish the configuration with:
```bash
php artisan vendor:publish --provider="Rapidez\Statamic\RapidezStatamicServiceProvider" --tag="config"
```

### Routing

As Rapidez uses route fallbacks to allow routes to be added with lower priority then Magento routes, this package is used to fix this, as statamic routes on itself will overwrite your Magento routes. Make sure default Statamic routing is disabled in `config/statamic/routes.php`:

```php
return [
    'enabled' => false,
    ...
];
```

### Fieldtypes

To use any of the field types this module has to offer enable them in `config/rapidez/statamic.php`:

```php
return [
    'field_types' => true
    ...
];
```
#### Products

At first place these 2 variables in your `.env`. `MIX_MAGENTO_URL` is just to pass your magento url to vue. `MIX_MAGENTO_TOKEN` is a permanent token you can get by adding an integration in the Magento backend under `System -> Integrations`.
```
MIX_MAGENTO_URL=${MAGENTO_URL}
MIX_MAGENTO_TOKEN=p5bpsgjvuvi80g7rh8tedtfmghyjbu1g
```

Add the products field type component in your resources/js/cp.js and also require axios if you don't already have:

```javascript
window.axios = require('axios')
import RapidezProducts from 'Vendor/rapidez/statamic/resources/js/components/fieldtypes/Rapidez_Products.vue';

Statamic.booting(() => {
    Statamic.$components.register('rapidez_products-fieldtype', RapidezProducts);
    ...
})
```

## License

GNU General Public License v3. Please see [License File](LICENSE) for more information.

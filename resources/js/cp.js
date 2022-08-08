window.axios = require('axios')
import RapidezProducts from 'Vendor/rapidez/statamic/resources/js/components/fieldtypes/Rapidez_Products.vue';

Statamic.booting(() => {
    Statamic.$components.register('rapidez_products-fieldtype', RapidezProducts);
})

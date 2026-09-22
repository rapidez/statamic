/* global Statamic */

import IndexerWidget from './components/widgets/IndexerWidget.vue';

Statamic.booting(() => {
    Statamic.$components.register('rapidez-indexer-widget', IndexerWidget);
});

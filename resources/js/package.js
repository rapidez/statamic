import 'Vendor/rapidez/core/resources/js/vue'

// Ensure Vue has access to global `Statamic` component
Vue.prototype.Statamic = window.Statamic;

// Create `<form-conditions>` component to be used with Vue JS driver
Vue.component('form-conditions', () => import('./components/FormConditions.vue'));

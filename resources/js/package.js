import 'Vendor/rapidez/core/resources/js/vue'

Vue.component('form-conditions', () => import('./components/FormConditions.vue'));

document.addEventListener('statamic:nocache.replaced', (e) => {
    window.app.csrfToken = e?.detail?.csrf ?? window.app.csrfToken
})
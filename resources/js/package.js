import 'Vendor/rapidez/core/resources/js/vue'

document.addEventListener('vue:loaded', (e) => {
    const vue = e.detail.vue
    vue.component('form-conditions', () => import('./components/FormConditions.vue'))
    vue.component('form-submission', () => import('./components/FormSubmission.vue'))
})

document.addEventListener('statamic:nocache.replaced', (e) => {
    const token = e?.detail?.csrf
    if (token) {
        if (window?.app?.config?.globalProperties) {
            window.app.config.globalProperties.csrfToken = token
        }
    }
})
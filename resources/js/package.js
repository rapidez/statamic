import 'Vendor/rapidez/core/resources/js/vue'
import FormConditions from './components/FormConditions.vue'
import FormSubmission from './components/FormSubmission.vue'

document.addEventListener('vue:loaded', (e) => {
    const vue = e.detail.vue
    vue.component('form-conditions', FormConditions)
    vue.component('form-submission', FormSubmission)
})

document.addEventListener('statamic:csrf.replaced', (e) => {
    const token = e?.detail?.csrf
    if (token) {
        if (window?.app?.config?.globalProperties) {
            window.app.config.globalProperties.csrfToken = token
        }
    }
})

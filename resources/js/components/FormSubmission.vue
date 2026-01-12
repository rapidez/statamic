<script>
import '/public/vendor/statamic/frontend/js/helpers.js'
if (window?.app?.config?.globalProperties) {
    window.app.config.globalProperties.Statamic = window.Statamic
}

export default {
    props: {
        callback: {
            type: Function,
        }
    },
    data() {
        return {
            success: false,
            error: false,
        };
    },
    render() {
        return this?.$slots?.default?.({
            submit: this.submit,
            success: this.success,
            error: this.error
        });
    },
    mounted() {
        let token = window?.app?.config?.globalProperties?.csrfToken
        let csrfField = this.$el.querySelector('input[value="STATAMIC_CSRF_TOKEN"]')

        if (csrfField && token && token !== 'STATAMIC_CSRF_TOKEN') {
            csrfField.value = token
        }
    },

    methods: {
        async submit(event) {
            event.preventDefault()

            let settings = {
                method: this.$el.method,
                body: new FormData(this.$el),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            const response = await rapidezFetch(this.$el.action, settings)

            let data = await response.json()

            if (response.ok) {
                this.success = true
                Notify(window.config.statamic.translations.form.success, 'success')
                if (this.callback) {
                    await this.callback()
                }
            } else {
                this.error = true
                Notify(window.config.statamic.translations.form.error, 'error')
                console.error(data?.errors)
            }
        },
    },
}
</script>

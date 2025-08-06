<script>
import '/public/vendor/statamic/frontend/js/helpers.js'
Vue.prototype.Statamic = window.Statamic

export default {
    props: {
        initialData: {
            type: Object,
            required: true,
        },
        callback: {
            type: Function,
        },
        ajaxResponse: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            formData: this.initialData,
            ajaxResponse: this.ajaxResponse,
            success: false,
            error: false,
        };
    },
    render() {
        return this.$scopedSlots.default(this);
    },
    mounted() {
        let token = this.$root.csrfToken
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
            };

            if(this.ajaxResponse) {
                settings.headers = {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }

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

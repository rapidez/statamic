<script>
import '/public/vendor/statamic/frontend/js/helpers.js'
Vue.prototype.Statamic = window.Statamic

export default {
    props: {
        initialData: {
            type: Object,
            required: true,
        },
        translations: {
            type: Object,
            default: () => ({}),
        },
        callback: {
            type: Function,
        },
    },
    data() {
        return {
            formData: this.initialData,
            success: false,
            error: false,
        };
    },
    render() {
        return this.$scopedSlots.default({ formData: this.formData, submit: this.submit });
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

            const response = await rapidezFetch(this.$el.action, {
                method: this.$el.method,
                body: new FormData(this.$el),
            })

            if (response.ok) {
                this.success = true
                Notify(this.translations.success, 'success')
                if (this.callback) {
                    await this.callback()
                }
            } else {
                this.error = true
                Notify(this.translations.error, 'error')
            }
        },
    },
}
</script>

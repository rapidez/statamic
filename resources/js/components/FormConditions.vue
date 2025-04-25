<script>
import '/public/vendor/statamic/frontend/js/helpers.js'
Vue.prototype.Statamic = window.Statamic

export default {
    props: ['initialData'],
    data() {
        return {
            formData: this.initialData,
        };
    },
    render() {
        return this.$scopedSlots.default({ formData: this.formData });
    },
    mounted() {
        let token = this.$root.csrfToken
        let csrfField = this.$el.querySelector('input[value="STATAMIC_CSRF_TOKEN"]')

        if (csrfField && token && token !== 'STATAMIC_CSRF_TOKEN') {
            csrfField.value = token
        }
    }
}
</script>

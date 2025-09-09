<script>
import '/public/vendor/statamic/frontend/js/helpers.js'
Vue.prototype.Statamic = window.Statamic

export default {
    props: {
        initialData: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            formData: this.initialData,
        };
    },
    render() {
        return this.$scopedSlots.default({
            formData: this.formData
        });
    },
}
</script>
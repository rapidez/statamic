<script>
import '/public/vendor/statamic/frontend/js/helpers.js'

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
        return this?.$slots?.default?.({
            formData: this.formData
        });
    },
}
</script>
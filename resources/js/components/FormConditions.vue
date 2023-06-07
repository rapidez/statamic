<script>
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
}
</script>
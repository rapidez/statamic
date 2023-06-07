import '/public/vendor/statamic/frontend/js/helpers.js'
import Vue from 'vue'

// Ensure Vue has access to global `Statamic` component
Vue.prototype.Statamic = window.Statamic;

// Create `<form-conditions>` component to be used with Vue JS driver
Vue.component('form-conditions', {
    props: ['initialData'],
    data() {
        return {
            formData: this.initialData,
        };
    },
    render() {
        return this.$scopedSlots.default({ formData: this.formData });
    },
});

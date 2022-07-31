<template>
    <div>
        <div class="relative">
            <div class="relative">
                <svg v-if="isOpen" @click="close()" class="h-5 w-5 absolute top-1/4 right-2 cursor-pointer" :class="{'top-[23%]': selected.length}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 19"><path fill="none" stroke="currentColor" stroke-width="2" d="M17 1.545 1.16 17.384m15.84 0L1.16 1.545"/></svg>
                <text-input @input="search" />
            </div>
            <div class="flex flex-wrap items-center gap-1 mt-1">
                <span v-for="item in selected" class="inline-flex rounded-full items-center py-0.5 pl-2.5 pr-1 text-sm font-medium bg-primary text-white">
                    {{ item }}
                    <button @click="toggleSelect(item)" type="button" class="flex-shrink-0 ml-0.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-white hover:text-grey focus:outline-none focus:text-white">
                        <span class="sr-only">Remove large option</span>
                        <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                            <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                        </svg>
                    </button>
                </span>
            </div>

        </div>
        <ul v-if="isOpen" class="max-h-[350px] overflow-scroll relative" :class="{'min-h-24 bg-white': isOpen}">
            <li class="flex  cursor-pointer p-2 my-1" v-if="loading">
                <loading-graphic :size="50" text="Searching..." />
            </li>
            <li class="flex items-center flex-row cursor-pointer p-2 my-1 hover:bg-gray-200" :class="{'bg-gray-200': selected.includes(item.sku)}" v-for="item in results" @click="toggleSelect(item)">
                <img class="h-12 w-12" :src="getImageForItem(item)" alt="">
                <div class="pl-1">
                    <span class="block text-md text-black">{{ item.name }}</span>
                    <span class="block text-sm text-gray-800">{{ item.sku }}</span>
                </div>
            </li>
        </ul>
    </div>

</template>

<script>
    export default {

        mixins: [Fieldtype],

        data() {
            return {
                results: {},
                selected: [],
                isOpen: false,
                loading: false,
            };
        },

        mounted() {
            this.selected = this.value.split(',')
        },

        watch: {
            selected(newSel, oldSel) {
                this.value = newSel.toString()
                this.update(this.value)
            }
        },


        methods: {
            close() {
                this.isOpen = !this.isOpen
            },

            getImageForItem(item) {
                return process.env.MIX_MAGENTO_URL + '/media/catalog/product/' + item.custom_attributes.find((attr) => attr.attribute_code == 'image')
            },

            toggleSelect(item) {
                let sku = typeof item == 'string' ? item : item.sku
                if (this.selected.includes(sku)) {
                    const index = this.selected.indexOf(sku);
                    this.selected.splice(index, 1);
                } else {
                    this.selected.push(sku)
                }
            },

            async search(value) {
                this.loading = true
                this.isOpen = true
                    await axios.get(process.env.MIX_MAGENTO_URL + '/rest/default/V1/products', {
                        params: {
                            'searchCriteria[pageSize]': 20,
                            'searchCriteria[filter_groups][0][filters][0][field]': 'sku',
                            'searchCriteria[filter_groups][0][filters][0][value]': '%'+value+'%',
                            'searchCriteria[filter_groups][0][filters][0][condition_type]': 'like',
                            'searchCriteria[filter_groups][0][filters][1][field]': 'name',
                            'searchCriteria[filter_groups][0][filters][1][value]': '%'+value+'%',
                            'searchCriteria[filter_groups][0][filters][1][condition_type]': 'like'
                        },
                        headers: {
                            'Authorization': 'Bearer ' + process.env.MIX_MAGENTO_TOKEN
                        }
                    }).then((res) => {
                        this.results = res.data.items

                        this.loading = false
                    })
            }
        }
};
</script>

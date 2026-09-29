<template>
    <Widget :title="title" icon="sync">
        <div class="flex items-center justify-between gap-4 px-4 py-3">
            <div class="min-w-0">
                <Description :text="labels.description" />
                <Text
                    class="mt-2"
                    size="xs"
                    variant="subtle"
                    :text="lastRunLabel"
                />
            </div>
            <Button
                size="sm"
                variant="primary"
                :text="labels.run"
                :disabled="busy"
                @click="confirming = true"
            />
        </div>

        <ConfirmationModal
            :open="confirming"
            :title="labels.confirm.title"
            :body-text="labels.confirm.body"
            :button-text="labels.confirm.button"
            :busy="busy"
            @update:open="onModalOpenChange"
            @confirm="runIndexer"
        >
            <div class="mt-4 space-y-4">
                <Field
                    v-if="typeOptions.length"
                    :label="labels.types"
                >
                    <Combobox
                        v-model="selectedTypes"
                        multiple
                        clearable
                        searchable
                        size="sm"
                        :options="typeOptions"
                        :placeholder="labels.types_placeholder"
                        :disabled="busy"
                    />
                </Field>

                <Field
                    v-if="storeOptions.length > 1"
                    :label="labels.stores"
                >
                    <Combobox
                        v-model="selectedStores"
                        multiple
                        clearable
                        searchable
                        size="sm"
                        :options="storeOptions"
                        :placeholder="labels.stores_placeholder"
                        :disabled="busy"
                    />
                </Field>
            </div>
        </ConfirmationModal>
    </Widget>
</template>

<script setup>
import { computed, getCurrentInstance, ref } from 'vue';
import { Button, Combobox, ConfirmationModal, Description, Field, Text, Widget } from '@statamic/cms/ui';

const { title, runUrl, lastIndexedAt, typeOptions, storeOptions, labels } = defineProps({
    title: { type: String, required: true },
    runUrl: { type: String, required: true },
    lastIndexedAt: { type: [String, null], default: null },
    typeOptions: { type: Array, default: () => [] },
    storeOptions: { type: Array, default: () => [] },
    labels: { type: Object, required: true },
});

const confirming = ref(false);
const busy = ref(false);
const selectedTypes = ref([]);
const selectedStores = ref([]);
const latestIndexedAt = ref(lastIndexedAt);
const app = getCurrentInstance()?.proxy;

const lastRunLabel = computed(() => {
    if (!latestIndexedAt.value) {
        return labels.never_run;
    }

    try {
        const formatted = new Date(latestIndexedAt.value).toLocaleString();

        return `${labels.last_run}: ${formatted}`;
    } catch {
        return labels.never_run;
    }
});

function onModalOpenChange(open) {
    if (!open && !busy.value) {
        confirming.value = false;
    }
}

async function runIndexer() {
    if (busy.value) {
        return;
    }

    busy.value = true;

    try {
        const response = await app.$axios.post(runUrl, {
            types: selectedTypes.value || [],
            stores: selectedStores.value || [],
        });
        const successKey = response.data?.queued ? 'queued' : 'completed';
        app.$toast.success(response.data?.message || labels.success[successKey]);

        if (!response.data?.queued) {
            latestIndexedAt.value = new Date().toISOString();
        }

        confirming.value = false;
    } catch {
        app.$toast.error(labels.error);
    } finally {
        busy.value = false;
    }
}
</script>

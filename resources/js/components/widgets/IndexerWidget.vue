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
            :title="labels.confirm_title"
            :body-text="labels.confirm_body"
            :button-text="labels.confirm_button"
            :busy="busy"
            @update:open="onModalOpenChange"
            @confirm="runIndexer"
        />
    </Widget>
</template>

<script setup>
import { computed, getCurrentInstance, ref } from 'vue';
import { Button, ConfirmationModal, Description, Text, Widget } from '@statamic/cms/ui';

const { title, runUrl, lastIndexedAt, labels } = defineProps({
    title: { type: String, required: true },
    runUrl: { type: String, required: true },
    lastIndexedAt: { type: [String, null], default: null },
    labels: { type: Object, required: true },
});

const confirming = ref(false);
const busy = ref(false);
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
        const response = await app.$axios.post(runUrl);
        app.$toast.success(response.data?.message || labels.success_completed);

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

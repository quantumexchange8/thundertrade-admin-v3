<template>
    <q-dialog :persistent="loading">
        <q-card class="!tw-w-[700px] !tw-max-w-[80vw] tw-flex tw-flex-col" :class="$attrs.cardClass"
            :style="$attrs.cardStyle">
            <q-card-section class="tw-flex tw-items-center tw-pb-0">
                <div>
                    <slot name="header"></slot>
                </div>
                <q-space />
                <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>
            <q-separator />

            <q-card-section class="tw-flex-grow scroll">
                <slot name="content" :loading="loading"></slot>

            </q-card-section>

            <q-separator />

            <slot name="footer"></slot>
            <q-inner-loading :showing="loading" label="Please Wait" />

        </q-card>

    </q-dialog>
</template>
<script setup>
const props = defineProps({
    loading: Boolean,
})
</script>
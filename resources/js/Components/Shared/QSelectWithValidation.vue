<template>
    <q-select :options="options" :multiple="multiple" :label="label" v-model="value" :error-message="errorMessage"
        :error="!!errorMessage" hide-bottom-space />
</template>

<script lang="ts" setup>
import { toRef } from "vue";
import { useField } from "vee-validate";

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    options: {
        type: Array,
        required: true,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
});

const { errorMessage, value } = useField(toRef(props, "name"));
</script>

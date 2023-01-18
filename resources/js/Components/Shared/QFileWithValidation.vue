<template>
    <q-file v-model="value" :error-message="errorMessage" :error="!!errorMessage" v-bind="$attrs" hide-bottom-space>
        <template v-for="(_, slot) of $slots" v-slot:[slot]="scope">
            <slot :name="slot" v-bind="{ ...scope }" />
        </template>

        <template v-slot:prepend>
            <q-icon name="attach_file" />
        </template>
    </q-file>
</template>
<script>
export default {
    inheritAttrs: false
}
</script>
<script setup>
import { toRef } from "vue";
import { useField } from "vee-validate";

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
});

const { errorMessage, value } = useField(toRef(props, "name"));
</script>

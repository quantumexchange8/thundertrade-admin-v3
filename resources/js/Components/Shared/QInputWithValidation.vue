<template>
    <q-input v-model="value" :error-message="errorMessage" :error="!!errorMessage" v-bind="$attrs"
        :placeholder="$attrs.placeholder ?? $attrs.label" hide-bottom-space>
        <template v-for="(_, slot) of $slots" v-slot:[slot]="scope">
            <slot :name="slot" v-bind="{ ...scope }" />
        </template>
    </q-input>
</template>
<script>
export default {
    inheritAttrs: false
}
</script>
<script setup >
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

<template>
    <q-field :borderless="true" :error="!!errorMessage" :error-message="errorMessage" hide-bottom-space>
        <template v-slot:control>
            <q-checkbox :label="label" v-model="value" v-bind="$attrs" />
        </template>
    </q-field>
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
    label: {
        type: String,
        required: true,
    },
});

const { errorMessage, value } = useField(toRef(props, "name"), undefined, {
    type: "checkbox",
    uncheckedValue: false,
    valueProp: true, // the checkbox "checked" value
});
</script>

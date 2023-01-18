<template>
    <q-field :borderless="true" :error="!!errorMessage" :error-message="errorMessage" hide-bottom-space v-bind="$attrs"
        dense>
        <template v-slot:control>
            <q-checkbox :label="label" v-model="value" v-bind="$attrs" dense>
                <template v-for="(_, slot) of $slots" v-slot:[slot]="scope">
                    <slot :name="slot" v-bind="{ ...scope }" />
                </template>
            </q-checkbox>
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
    checkedValue: true,
    uncheckedValue: false,
    valueProp: true, // the checkbox "checked" value
});
</script>

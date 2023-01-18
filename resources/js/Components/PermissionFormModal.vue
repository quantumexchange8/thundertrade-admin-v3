<template>
    <Modal>
        <template #header>
            <div class="text-h6">{{ title }}</div>
        </template>
        <template #content>
            <QInputWithValidation name="name" label="Name" />
            <QSelectWithValidation name="group_id" label="Group Name" :options="options" emit-value map-options
                option-label="name" option-value="id" :readonly="action == 'update'" />
        </template>
        <template #footer>
            <q-card-actions align="right">
                <q-btn label="Close" v-close-popup />
                <q-btn label="Submit" @click="onSubmit" />
            </q-card-actions>
        </template>
    </Modal>
</template>
<script setup>
import axios from "axios";
import { useQuasar } from "quasar";
import { useForm } from "vee-validate";
import * as yup from "yup";
import { toRef, computed, useAttrs, watch, ref } from "vue";
const props = defineProps({
    action: String,
    data: Object,
})
const emit = defineEmits(['closeModal'])
const title = computed(() => props.action == 'create' ? 'Create Permission' : 'Update Permission');
const $q = useQuasar();
const modelValue = toRef(useAttrs(), 'modelValue');
const options = ref([]);
watch(modelValue, async (newVal) => {
    if (newVal) {
        if (props.action == 'update') {
            const { data } = await axios.get(route('permissions.edit', { permission: props.data.id }));
            if (data.success) {
                options.value = data.data.groups;
                form.setValues(data.data.details);
            }
        } else {
            const { data } = await axios.get(route('permissions.create'));
            if (data.success) {
                options.value = data.data.groups;
            }
        }
    }


});

const schema = yup.object({
    name: yup.string().required().label('Name'),
    group_id: yup.number().required().label('Group ID'),
});


const form = useForm({
    initialValues: {
        name: '',
        group_id: '',
    }
    ,
    validationSchema: schema,
})


const onSubmit = () => {
    if (props.action == 'update') {
        axios.put(route('permissions.update', { permission: props.data.id }), form.values).then(response => {
            if (response.data.success) {
                $q.notify({ type: 'positive', message: response.data.message })
            } else {
                $q.notify({ type: 'negative', message: response.data.message })
            }
            emit('closeModal');
        }).catch(err => console.error(err));
    } else {
        axios.post(route('permissions.store'), form.values).then(response => {
            if (response.data.success) {
                $q.notify({ type: 'positive', message: response.data.message })
            } else {
                $q.notify({ type: 'negative', message: response.data.message })
            }
            emit('closeModal');
        }).catch(err => console.error(err));
    }


}
</script>
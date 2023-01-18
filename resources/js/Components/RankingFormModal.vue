<template>
    <Modal>
        <template #header>
            <div class="text-h6">{{ title }}</div>
        </template>
        <template #content>
            <QInputWithValidation name="level" label="Level" />
            <QInputWithValidation name="amount" label="Amount" />
            <QInputWithValidation name="deposit" label="Deposit Fee" />
            <QInputWithValidation name="withdrawal" label="Withdrawal Fee" />
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
const title = computed(() => props.action == 'create' ? 'Create Ranking' : 'Update Ranking');
const $q = useQuasar();
const modelValue = toRef(useAttrs(), 'modelValue');

watch(modelValue, async (newVal) => {
    if (newVal) {
        if (props.action == 'update') {
            const { data } = await axios.get(route('rankings.edit', { ranking: props.data.id }));
            if (data.success) {
                form.setValues(data.data);
            }
        }
    }


});

const schema = yup.object({
    level: yup.string().required().label('Level'),
    amount: yup.number().required().label('Amount'),
    deposit: yup.number().required().label('Deposit Fee'),
    withdrawal: yup.number().required().label('Withdrawal Fee'),
});



const form = useForm({
    initialValues: {
        level: '',
        amount: 0,
        deposit: 0,
        withdrawal: 0,
    }
    ,
    validationSchema: schema,
})


const onSubmit = () => {
    if (props.action == 'update') {
        axios.put(route('rankings.update', { ranking: props.data.id }), form.values).then(response => {
            if (response.data.success) {
                $q.notify({ type: 'positive', message: response.data.message })
            } else {
                $q.notify({ type: 'negative', message: response.data.message })
            }
            emit('closeModal');
        }).catch(err => console.error(err));
    } else {
        axios.post(route('rankings.store'), form.values).then(response => {
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
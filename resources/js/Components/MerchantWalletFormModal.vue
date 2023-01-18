<template>
    <Modal>
        <template #header>
            <div class="text-h6">{{ title }}</div>
        </template>
        <template #content>
            <QInputWithValidation name="merchant.name" label="Merchant Name" readonly />
            <QInputWithValidation name="type" label="Type" readonly />
            <QInputWithValidation name="wallet_address" label="Wallet Address" />
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
const title = computed(() => props.action == 'create' ? 'Create Merchant Wallet' : 'Update Merchant Wallet');
const $q = useQuasar();
const modelValue = toRef(useAttrs(), 'modelValue');
watch(modelValue, async (newVal) => {
    if (newVal) {
        if (props.action == 'update') {
            const { data } = await axios.get(route('merchants.wallets.edit', { merchant: route().params.merchant, wallet: props.data.id }));
            if (data.success) {
                form.setValues(data.data);
            }
        }
    }


});

const schema = yup.object({
    wallet_address: yup.string().nullable().label('Wallet Address'),
});


const form = useForm({
    initialValues: {
        wallet_address: '',
    }
    ,
    validationSchema: schema,
})


const onSubmit = () => {
    if (props.action == 'update') {
        axios.put(route('merchants.wallets.update', { merchant: route().params.merchant, wallet: props.data.id }), form.values).then(response => {
            if (response.data.success) {
                $q.notify({ type: 'positive', message: response.data.message })
            } else {
                $q.notify({ type: 'negative', message: response.data.message })
            }
            emit('closeModal');
        }).catch(err => console.error(err));
    } else {
        axios.post(route('merchants.wallets.store', { merchant: route().params.merchant }), form.values).then(response => {
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
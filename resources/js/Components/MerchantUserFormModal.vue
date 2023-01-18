<template>
    <Modal>
        <template #header>
            <div class="text-h6">{{ title }}</div>
        </template>
        <template #content>
            <QInputWithValidation name="email" label="Email" />
            <QInputWithValidation name="name" label="Name" />
            <QInputWithValidation name="phone" label="Phone" />
            <QFileWithValidation name="file_profile_picture" label="Profile Picture" />
            <QSelectWithValidation name="role_id" label="Role Name" :options="roleOptions" emit-value map-options
                option-label="name" option-value="id" />
            <QInputWithValidation name="password" label="Password">
                <template v-slot:after>
                    <q-btn outline label="Generate" @click="generatePassword()" />
                </template>
            </QInputWithValidation>
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
const title = computed(() => props.action == 'create' ? 'Create User' : 'Update User');
const $q = useQuasar();
const modelValue = toRef(useAttrs(), 'modelValue');
const roleOptions = ref([]);
watch(modelValue, async (newVal) => {
    if (newVal) {
        roleOptions.value = [];
        if (props.action == 'update') {
            const { data } = await axios.get(route('merchants.users.edit', { merchant: route().params.merchant, user: props.data.id }));
            if (data.success) {
                roleOptions.value = data.data.roles;
                form.setValues(data.data.details);
            }
        } else {
            const { data } = await axios.get(route('merchants.users.create', { merchant: route().params.merchant }));
            if (data.success) {
                roleOptions.value = data.data.roles;
            }
        }
    }
});


const schema = yup.object({
    email: yup.string().email().required().label('Email'),
    name: yup.string().required().label('Name'),
    phone: yup.number().required().label('Phone'),
    file_profile_picture: yup.mixed().nullable().label('Profile Picture'),
    role_id: yup.number().required().label('Role ID'),
    password: yup.string().nullable().label('Password')
});


const form = useForm({
    initialValues: {
        email: '',
        name: '',
        phone: '',
        file_profile_picture: '',
        role_id: '',
        password: '',
    }
    ,
    validationSchema: schema,
})


const onSubmit = () => {
    let fd = new FormData;
    Object.entries(form.values).forEach(entry => {
        const [key, value] = entry;
        fd.append(key, value);
    })
    if (props.action == 'update') {
        fd.append('_method', 'PUT');
        axios.post(route('merchants.users.update', { merchant: route().params.merchant, user: props.data.id }), fd).then(response => {
            if (response.data.success) {
                $q.notify({ type: 'positive', message: response.data.message })
            } else {
                $q.notify({ type: 'negative', message: response.data.message })
            }
            emit('closeModal');
        }).catch(err => console.error(err));
    } else {
        axios.post(route('merchants.users.store', { merchant: route().params.merchant }), fd).then(response => {
            if (response.data.success) {
                $q.notify({ type: 'positive', message: response.data.message })
            } else {
                $q.notify({ type: 'negative', message: response.data.message })
            }
            emit('closeModal');
        }).catch(err => console.error(err));
    }


}

const generatePassword = () => {
    let chars = "0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*()ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    let passwordLength = 8;
    let password = "";

    for (let i = 0; i <= passwordLength; i++) {
        let randomNumber = Math.floor(Math.random() * chars.length);
        password += chars.substring(randomNumber, randomNumber + 1);
    }
    form.setFieldValue('password', password);
}
</script>
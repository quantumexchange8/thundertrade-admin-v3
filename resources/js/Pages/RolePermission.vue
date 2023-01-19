<template>

    <Head title="Role Permission" />
    <div class="q-pa-md">
        <Form ref="form" @submit="onSubmit">
            <q-btn label="Save Permission" color="primary" type="submit" />

            <q-list bordered padding>
                <template v-for="group in permissions" :key="group.id">
                    <q-item-label header>{{ group.name }}</q-item-label>
                    <q-item tag="label" v-ripple v-for="permission in group.permissions" :key="permission.id">
                        <q-item-section>
                            <q-item-label>
                                <QCheckboxWithValidation name="permissions" :label="permission.name"
                                    :val="permission.code" />
                            </q-item-label>
                        </q-item-section>
                    </q-item>
                </template>
            </q-list>
        </Form>
    </div>

</template>
<script setup>
import * as yup from "yup";
import { useForm, Form } from "vee-validate";
import { onMounted, ref } from "vue";
import axios from "axios";
import { useQuasar } from "quasar";
const $q = useQuasar();
const props = defineProps({
    permissions: Array,
    rolePermissions: Array,
})
const form = ref(null)

console.log(props.permissions)
console.log(props.rolePermissions);
onMounted(() => {
    form.value.setFieldValue('permissions', props.rolePermissions);
})

const onSubmit = (values, action) => {
    axios.post(route('roles.permissions.store', { role: route().params.role }), values).then(response => {
        if (response.data.success) {
            $q.notify({ type: 'positive', message: response.data.message })
        } else {
            $q.notify({ type: 'negative', message: response.data.message })
        }
    }).catch(err => console.error(err));
}
</script>

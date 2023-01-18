<template>

    <Head title="Login" />
    <div class="tw-h-screen tw-flex tw-flex-col tw-justify-center">
        <q-card class="md:tw-w-1/2 tw-mx-auto tw-text-center tw-px-8 tw-py-16" dark>
            <Form :initial-values="forgotPasswordInitialValues" :validation-schema="forgotPasswordSchema"
                @submit="onForgotPassword" autocomplete="off" v-if="forgot">

                <q-card-section class="tw-flex tw-justify-center">
                    <img src="/img/thunderTrade.png" height="100" width="100">
                    <div class="tw-text-3xl tw-flex tw-flex-col tw-justify-center">ThunderTrade</div>
                </q-card-section>

                <q-card-section class="tw-space-y-4">
                    <QInputWithValidation name="email" label="Email" dark v-model="email" />
                    <QInputWithValidation name="otp" label="OTP" :readonly="!send" dark>
                        <template v-slot:append>
                            <q-btn outline label="Send" @click="sendOTP(); $page.props.flash.message = null" />
                        </template>
                    </QInputWithValidation>
                </q-card-section>
                <q-card-section class="tw-text-right">
                    <q-btn flat label="Back To Login" @click="forgot = false; form.reset();" />
                </q-card-section>
                <q-card-section>
                    <q-btn color="yellow" text-color="black" type="submit" label="Reset Password" />
                </q-card-section>
            </Form>
            <Form ref="form" :initial-values="initialValues" :validation-schema="schema" @submit="onSubmit"
                autocomplete="off" v-else>

                <q-card-section class="tw-flex tw-justify-center">
                    <img src="/img/thunderTrade.png" height="100" width="100">
                    <div class="tw-text-3xl tw-flex tw-flex-col tw-justify-center">ThunderTrade</div>
                </q-card-section>

                <q-card-section class="tw-space-y-4">
                    <QInputWithValidation name="email" label="Email" dark />
                    <QInputWithValidation name="password" label="Password" :type="isPwd ? 'password' : 'text'" dark>
                        <template v-slot:append>
                            <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                                @click="isPwd = !isPwd" />
                        </template>
                    </QInputWithValidation>
                </q-card-section>
                <q-card-section class="tw-flex tw-justify-between">
                    <div class="tw-space-x-2 tw-flex">
                        <QCheckboxWithValidation dark name="remember" label="Remember Me" />
                    </div>
                    <q-btn flat label="Forgot Password?" @click="forgot = true" />
                </q-card-section>
                <q-card-section>
                    <q-btn color="yellow" text-color="black" type="submit" label="Login" />
                </q-card-section>
            </Form>
        </q-card>
    </div>
</template>
<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import { Form } from "vee-validate";
import * as yup from "yup";
import axios from "axios";
import { Inertia } from "@inertiajs/inertia";
const $q = useQuasar();
const forgot = ref(false);
const send = ref(false);
const email = ref(null);
const onForgotPassword = (values, action) => {
    axios.post('forgot-password', values).then(res => {
        if (res.data.success) {
            $q.notify({ type: 'positive', message: res.data.message });
        } else {
            $q.notify({ type: 'negative', message: res.data.message });
        }
    }).catch(err => {
        console.log(err);
        $q.notify({
            type: 'warning',
            message: err
        })
    })
}

const onSubmit = async (values, acction) => {
    axios.post('login', values)
        .then(res => {
            if (res.data.success) {
                $q.notify({
                    'type': "positive",
                    "message": "Login Success",
                });
                Inertia.visit(route('merchants.index'));
            } else {
                $q.notify({ type: 'negative', message: res.data.message });
            }
        })
        .catch(err => {
            console.log(err);
            $q.notify({
                type: 'warning',
                message: err
            })
        })
}

const initialValues = {
    email: '',
    password: "",
    remember: true
}

const forgotPasswordInitialValues = {
    email: '',
    otp: '',
};

const schema = yup.object({
    email: yup.string().email().required().label('Email'),
    password: yup.string().required().label('Password'),
});

const forgotPasswordSchema = yup.object({
    email: yup.string().email().required().label('Email'),
    otp: yup.number().min(100000).max(999999).required().label('OTP')
});

const sendOTP = () => {
    axios.post('otp-create', {
        'email': email.value,
        'action': 'forgot_password'
    }
    ).then((response) => {
        send.value = true;
        $q.notify({
            type: 'positive',
            message: response.data.message
        })
    }).catch(error => {
        $q.notify({
            type: 'negative',
            message: error.response.data.message
        })
    });
}
const isPwd = ref(true)
</script>

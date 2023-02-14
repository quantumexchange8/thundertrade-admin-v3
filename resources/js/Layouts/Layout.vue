<template>
    <q-layout view="hHh LpR fff">

        <q-header elevated class="bg-black text-white">
            <q-toolbar>
                <q-btn dense flat round icon="menu" @click="toggleLeftDrawer" />

                <q-toolbar-title>
                    <q-avatar square>
                        <img src="/img/thunderTrade.png">
                    </q-avatar>
                    ThunderTrade
                </q-toolbar-title>
                <q-btn label="LogOut" @click="logout()" />
            </q-toolbar>
        </q-header>

        <q-drawer show-if-above v-model="leftDrawerOpen" class="bg-dark" side="left" bordered>
            <!--     <AppMenu :menu="Menu" /> -->
        </q-drawer>

        <q-page-container class="tw-h-screen">
            <q-page padding>
                <slot></slot>
            </q-page>

        </q-page-container>

    </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import { Inertia } from "@inertiajs/inertia";
import Menu from "./menu.json";
import AppMenu from "./AppMenu"
const leftDrawerOpen = ref(false)

const toggleLeftDrawer = () => {
    leftDrawerOpen.value = !leftDrawerOpen.value
}

const logout = () => {
    Inertia.post('/logout')
}
</script>

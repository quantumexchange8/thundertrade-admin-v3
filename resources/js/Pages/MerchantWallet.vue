<template>

    <Head title="Wallet" />
    <div class="tw-flex tw-justify-between">
        <div class="tw-flex tw-content-center tw-space-x-2">
            <QInputWithValidation name="merchant.name" label="Merchant Name" v-model="search['merchant.name']" dense />
            <QInputWithValidation name="start_date" label="Start Date" stack-label
                v-model="search.created_at.startDate.value" type="date" dense />
            <QInputWithValidation name="end_date" label="End Date" stack-label v-model="search.created_at.endDate.value"
                type="date" dense />
        </div>
        <div class="tw-p-2">
            <q-btn label="Search" outline @click="tableData.refresh()" />
        </div>
    </div>
    <Table :columns="columns" :url="url" ref="tableData" :search="search">
        <template #body-cell-actions="slotProps">
            <q-td class="text-center">
                <ActionButtonDropdown :data="slotProps.row" :lists="actionLists" @itemClick="onItemClick" />
            </q-td>
        </template>
    </Table>

    <MerchantWalletFormModal v-model="formModal" :action="formAction" :data="rowData"
        @closeModal="closeMerchantWalletFormModal" />
</template>
<script setup>
import { ref } from "vue";
import MerchantWalletFormModal from "@/Components/MerchantWalletFormModal.vue";
const formModal = ref(false);
const formAction = ref(null);
const rowData = ref(null);


const search = ref({
    mode: 'or',
    'merchant.name': '',
    created_at: {
        mode: 'and',
        type: 'date',
        startDate: {
            symbol: '>=',
            value: ''
        },
        endDate: {
            symbol: '<=',
            value: ''
        }
    },

})

const tableData = ref(null);


const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'merchant.name', label: 'Merchant Name', field: row => row.merchant.name, sortable: true, align: 'left', foreign: true },
    { name: 'wallet_number', label: 'Wallet Number', field: 'wallet_number', sortable: true, align: 'left' },
    { name: 'deposit_balance', label: 'Deposit Balance', field: 'deposit_balance', sortable: true, align: 'left' },
    { name: 'gross_deposit', label: 'Gross Deposit', field: 'gross_deposit', sortable: true, align: 'left' },
    { name: 'gross_withdrawal', label: 'Gross Withdrawal', field: 'gross_withdrawal', sortable: true, align: 'left' },
    { name: 'wallet_address', label: 'Wallet Address', field: 'wallet_address', sortable: true, align: 'left' },
    { name: 'type', label: 'Type', field: 'type', sortable: true, align: 'left' },
    { name: 'actions', label: 'Actions', field: 'actions', sortable: false, align: 'left', hidden: true },
]

const url = `/table/merchants/${route().params.merchant}/wallets`;


const actionLists = [
    { label: 'Edit', value: 'edit' },
]
const onItemClick = (action, data) => {
    if (action == "edit") {
        openMerchantWalletFormModal('update', data);
    }
}

const openMerchantWalletFormModal = (action, data = null) => {
    rowData.value = data;
    formAction.value = action;
    formModal.value = true;
}
const closeMerchantWalletFormModal = () => {
    formModal.value = false;
    tableData.value.refresh();
}



</script>

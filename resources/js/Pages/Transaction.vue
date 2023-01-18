<template>

    <Head title="Merchant Transaction" />
    <Table :columns="columns" :url="url" ref="tableData">
        <template #body-cell-actions="slotProps">
            <q-td class="text-center">
                <ActionButtonDropdown :data="slotProps.row" :lists="actionLists" @itemClick="onItemClick" />
            </q-td>
        </template>
    </Table>
</template>
<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";

import axios from "axios";


const tableData = ref(null);
const $q = useQuasar();

const actionLists = [
    { label: 'Approve', value: 'approve' },
    { label: 'Reject', value: 'reject' },


]
const onItemClick = (action, data) => {
    if (action == "approve" || action == "reject") {
        $q.dialog({
            title: 'Confiramtion',
            message: `Are you sure u want to ${action} ${data.transaction_no}?`,
            prompt: {
                model: '',
                label: 'Approval Reason',
                name: 'approval_reason',
                type: 'textarea',
            }
        }).onOk(content => {
            const status = action == "approve" ? 2 : 1;
            axios.put(route('transactions.update', { transaction: data.id }), {
                status: status,
                approval_reason: content,
            }).then(response => {
                if (response.data.success) {
                    $q.notify({ type: 'positive', message: response.data.message })
                } else {
                    $q.notify({ type: 'negative', message: response.data.message })
                }
                tableData.value.refresh();
            }).catch(err => console.error(err));
        })
    }
}


const columns = [
    {
        name: 'id',
        label: 'ID',
        field: 'id',
        sortable: true,
        align: 'left'
    },
    {
        name: 'status',
        label: 'Status',
        field: 'status',
        sortable: true,
        align: 'left'
    },
    {
        name: 'transaction_type',
        label: 'Transaction Type',
        field: 'transaction_type',
        sortable: true,
        align: 'left'
    },
    {
        name: 'amount',
        label: 'Amount',
        field: 'amount',
        sortable: true,
        align: 'left'
    },
    {
        name: 'charges',
        label: 'Charges',
        field: 'charges',
        sortable: true,
        align: 'left'
    },
    {
        name: 'total',
        label: 'Total',
        field: 'total',
        sortable: true,
        align: 'left'
    },
    {
        name: 'wallet.wallet_number',
        label: 'Wallet Number',
        field: row => row.wallet.wallet_number,
        sortable: true,
        align: 'left'
    },
    {
        name: 'transaction_no',
        label: 'Transaction Number',
        field: 'transaction_no',
        sortable: true,
        align: 'left'
    },
    {
        name: 'merchant.name',
        label: 'Merchant Name',
        field: row => row.merchant.name,
        sortable: true,
        align: 'left'
    },
    {
        name: 'receipt',
        label: 'Receipt',
        field: 'receipt',
        sortable: true,
        align: 'left'
    },
    {
        name: 'remarks',
        label: 'Remarks',
        field: 'remarks',
        sortable: true,
        align: 'left'
    },
    {
        name: 'approval_date',
        label: 'Approval Date',
        field: 'approval_date',
        sortable: true,
        align: 'left'
    },
    {
        name: 'approval_by',
        label: 'Approval By',
        field: 'approval_by',
        sortable: true,
        align: 'left'
    },
    {
        name: 'approval_reason',
        label: 'Approval Reason',
        field: 'approval_reason',
        sortable: true,
        align: 'left'
    },
    {
        name: 'merchant_transaction_id',
        label: 'Merchant Transaction ID',
        field: 'merchant_transaction_id',
        sortable: true,
        align: 'left'
    },
    {
        name: 'actions',
        label: 'Actions',
        field: 'actions',
        sortable: false,
        align: 'left',
        hidden: true
    },
]

const url = '/table/transactions';

</script>

<template>

    <Head title="User Transaction" />
    <div class="tw-flex tw-justify-between">
        <div class="tw-flex tw-content-center tw-space-x-2">
            <QSelectWithValidation name="status" label="Status" v-model="search.status" :options="options" emit-value
                map-options style="width:200px" />
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
        <template #body-cell-receipt="props">
            <q-td class="tw-space-x-1" :props="props">
                <img :src="'/' + props.value" @click="img = true; src = `/${props.value}`">
            </q-td>
        </template>
    </Table>

    <q-dialog v-model="img" maximized id="image">
        <q-img :src="src" width="90%" height="auto" />
    </q-dialog>
</template>
<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";

const img = ref(false);
const src = ref();
const options = ['', 'Pending', 'Approved', 'Rejected'];

const search = ref({
    mode: 'and',
    status: '',
})

const tableData = ref(null);
const $q = useQuasar();

const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'user.name', label: 'User Name', field: row => row.user.name, sortable: true, align: 'left' },
    { name: 'merchant.name', label: 'Merchant Name', field: row => row.merchant.name, sortable: true, align: 'left' },
    { name: 'status', label: 'Status', field: 'status', sortable: true, align: 'left' },
    { name: 'transaction_type', label: 'Transaction Type', field: 'transaction_type', sortable: true, align: 'left' },
    { name: 'amount', label: 'Amount', field: 'amount', sortable: true, align: 'left' },
    { name: 'charges', label: 'Charges', field: 'charges', sortable: true, align: 'left' },
    { name: 'total', label: 'Total', field: 'total', sortable: true, align: 'left' },
    { name: 'wallet.number', label: 'Wallet Number', field: row => row.wallet.wallet_number, sortable: true, align: 'left' },
    { name: 'transaction_no', label: 'Transaction Number', field: 'transaction_no', sortable: true, align: 'left' },
    { name: 'receipt', label: 'Receipt', field: 'receipt', sortable: true, align: 'left' },
    { name: 'remarks', label: 'Remarks', field: 'remarks', sortable: true, align: 'left' },
    { name: 'approval_date', label: 'Approval Date', field: 'approval_date', sortable: true, align: 'left' },
    { name: 'adminUser.name', label: 'Approved By', field: row => row.admin_user?.name, sortable: true, align: 'left' },
    { name: 'reject_reason', label: 'Reject Reason', field: 'reject_reason', sortable: true, align: 'left' },
    { name: 'actions', label: 'Actions', field: 'actions', sortable: false, align: 'left', hidden: true },
]

const url = '/table/usertransactions';


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
            axios.put(route('usertransactions.update', { transaction: data.id }), {
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


</script>
<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
::-webkit-scrollbar {
    display: none;
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}
</style>

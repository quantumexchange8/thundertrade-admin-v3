<template>

    <Head title="Merchant" />
    <div class="tw-flex tw-justify-between">
        <div class="tw-flex tw-content-center tw-space-x-2">
            <QInputWithValidation name="name" label="Name" v-model="search.name" />
            <QInputWithValidation name="start_date" label="Start Date" stack-label
                v-model="search.created_at.startDate.value" type="date" />
            <QInputWithValidation name="end_date" label="End Date" stack-label v-model="search.created_at.endDate.value"
                type="date" />
        </div>
        <div class="tw-p-2">
            <q-btn label="Search" outline @click="tableData.refresh()" />
        </div>
    </div>
    <Table :columns="columns" :url="url" ref="tableData" :search="search">
        <template #top-left1>
            <q-btn outline label="create" @click="openMerchantFormModal('create')" />
        </template>

        <template #body-cell-actions="slotProps">
            <q-td class="text-center">
                <ActionButtonDropdown :data="slotProps.row" :lists="actionLists" @itemClick="onItemClick" />
            </q-td>
        </template>


    </Table>
    <MerchantFormModal v-model="formModal" :action="formAction" :data="rowData" @closeModal="closeMerchantFormModal" />
</template>
<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import { Inertia } from "@inertiajs/inertia";
import MerchantFormModal from "@/Components/MerchantFormModal.vue";
const id = ref();
const formModal = ref(false);
const formAction = ref(null);
const rowData = ref(null);
const tableData = ref(null);
const $q = useQuasar();


const search = ref({
    mode: 'and',
    name: '',
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




const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'name', label: 'Name', field: 'name', sortable: true, align: 'left' },
    { name: 'notify_url', label: 'Notify Url', field: 'notify_url', sortable: true, align: 'left' },
    { name: 'api_key', label: 'Api Key', field: 'api_key', sortable: true, align: 'left' },
    { name: 'ranking.level', label: 'Ranking', field: row => row.ranking.level, sortable: true, align: 'left' },
    { name: 'actions', label: 'Actions', field: 'actions', sortable: false, align: 'left', hidden: true },
]

const url = '/table/merchants';


const actionLists = [
    { label: 'Edit', value: 'edit' },
    //{ label: 'Delete', value: 'delete' },
    { label: 'Manage User', value: 'manage-user' },
    { label: 'Manage Role', value: 'manage-role' },
    { label: 'Manage Wallet', value: 'manage-wallet' },
    { label: 'Manage Transction', value: 'manage-transaction' },
    { label: 'Manage User Transction', value: 'manage-user-transaction' },

]
const onItemClick = (action, data) => {
    if (action == "edit") {
        openMerchantFormModal('update', data);
    } else if (action == 'delete') {
        deleteMerchant(data);
    } else if (action == 'manage-user') {
        Inertia.visit(route('merchants.users.index', { merchant: data.id }));
    } else if (action == "manage-role") {
        Inertia.visit(route('merchants.roles.index', { merchant: data.id }));
    }
    else if (action == 'manage-wallet') {
        Inertia.visit(route('merchants.wallets.index', { merchant: data.id }));
    } else if (action == "manage-transaction") {
        Inertia.visit(route('merchants.transactions.index', { merchant: data.id }));
    }
    else if (action == 'manage-user-transaction') {
        Inertia.visit(route('merchants.usertransactions.index', { merchant: data.id }));
    }
}

const openMerchantFormModal = (action, data = null) => {
    rowData.value = data;
    formAction.value = action;
    formModal.value = true;
}
const closeMerchantFormModal = () => {
    formModal.value = false;
    tableData.value.refresh();
}

const deleteMerchant = (data) => {
    $q.dialog({
        title: 'Are you sure?',
        message: `Are you srue you want to delete ${data.name}?`
    }).onOk(() => {

        axios.delete(route('merchants.destory', { merchant: data.id }))
            .then(response => {
                if (response.data.success) {
                    $q.notify({
                        type: 'positive',
                        message: response.data.message
                    })
                    tableData.value.refresh();
                } else {
                    $q.notify({
                        type: 'negative',
                        message: response.data.message
                    })
                }
                tableData.value.refresh();
            }).catch(err => console.error(err));
    })
}

</script>

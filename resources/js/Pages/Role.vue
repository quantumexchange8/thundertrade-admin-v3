<template>

    <Head title="Role" />
    <Table :columns="columns" :url="url" ref="tableData">
        <template #top-left1>
            <q-btn outline label="create" @click="openRoleFormModal('create')" />
        </template>
        <template #body-cell-actions="slotProps">
            <q-td class="text-center">
                <ActionButtonDropdown :data="slotProps.row" :lists="actionLists" @itemClick="onItemClick" />
            </q-td>
        </template>
    </Table>
    <RoleFormModal v-model="formModal" :action="formAction" :data="rowData" @closeModal="closeRoleFormModal" />

</template>
<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import { Inertia } from "@inertiajs/inertia";
import RoleFormModal from "@/Components/RoleFormModal.vue";

const id = ref();
const formAction = ref(null);
const formModal = ref(false);
const rowData = ref(null);

const tableData = ref(null);
const $q = useQuasar();

const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'name', label: 'Name', field: 'name', sortable: true, align: 'left' },
    { name: 'merchant.name', label: 'Merchant Name', field: row => row.merchant?.name, sortable: true, align: 'left' },
    { name: 'actions', label: 'Actions', field: 'actions', sortable: false, align: 'left', hidden: true },
]

const url = '/table/roles';

const openRoleFormModal = (action, data = null) => {
    rowData.value = data;
    formAction.value = action;
    formModal.value = true;
}

const closeRoleFormModal = () => {
    formModal.value = false;
    tableData.value.refresh();
}

const actionLists = [
    { label: 'Delete', value: 'delete' },
    { label: 'Manage Permission', value: 'manage-permission' },
]
const onItemClick = (action, data) => {
    if (action == 'delete') {
        deleteRole(data);
    } else if (action == "manage-permission") {
        Inertia.visit(route('roles.permissions.index', { role: data.id }))
    }
}

const deleteRole = (data) => {
    $q.dialog({
        title: 'Are you sure?',
        message: `Are you srue you want to delete ${data.name}?`
    }).onOk(() => {
        axios.delete(route('roles.destroy', { role: data.id }))
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

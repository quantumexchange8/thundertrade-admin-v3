<template>

    <Head title="User" />
    <Table :columns="columns" :url="url" ref="tableData">
        <template #top-left1>
            <q-btn outline label="create" @click="openUserFormModal('create')" />
        </template>
        <template #body-cell-actions="slotProps">
            <q-td class="text-center">
                <ActionButtonDropdown :data="slotProps.row" :lists="actionLists" @itemClick="onItemClick" />
            </q-td>
        </template>
    </Table>
    <UserFormModal v-model="formModal" :action="formAction" :data="rowData" @closeModal="closeUserFormModal" />
</template>
<script setup>
import { ref, watch } from "vue";
import { useQuasar } from "quasar";
import UserFormModal from "@/Components/UserFormModal.vue";


const tableData = ref(null);

const $q = useQuasar();

const formAction = ref(null);
const formModal = ref(false);
const rowData = ref(null);

const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'email', label: 'Email', field: 'email', sortable: true, align: 'left' },
    { name: 'name', label: 'Name', field: 'name', sortable: true, align: 'left' },
    { name: 'phone', label: 'Phone', field: 'phone', sortable: true, align: 'left' },
    { name: 'profile_picture', label: 'Profile Picture', field: 'profile_picture', sortable: true, align: 'left' },
    { name: 'role.name', label: 'Role Name', field: row => row.role.name, sortable: true, align: 'left', foreign: true },
    { name: 'merchant.name', label: 'Merchant Name', field: row => row.merchant?.name, sortable: true, align: 'left', foreign: true },
    { name: 'actions', label: 'Actions', field: 'actions', sortable: false, align: 'left', hidden: true },
]

const url = '/table/users';


const openUserFormModal = (action, data = null) => {
    rowData.value = data;
    formAction.value = action;
    formModal.value = true;
}

const closeUserFormModal = () => {
    formModal.value = false;
    tableData.value.refresh();
}

const actionLists = [
    { label: 'Edit', value: 'edit' },
    { label: 'Delete', value: 'delete' },
]
const onItemClick = (action, data) => {
    if (action == "edit") {
        openUserFormModal('update', data);
    } else if (action == 'delete') {
        deleteUser(data);
    }
}

const deleteUser = (data) => {
    $q.dialog({
        title: 'Are you sure?',
        message: `Are you srue you want to delete ${data.name}?`
    }).onOk(() => {
        axios.delete(route('users.destroy', { user: data.id }))
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

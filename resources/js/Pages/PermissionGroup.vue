<template>

    <Head title="Permission Group" />
    <Table :columns="columns" :url="url" ref="tableData">
        <template #top-left1>
            <q-btn outline label="create" @click="openPermissionGroupFormModal('create')" />
        </template>
        <template #body-cell-actions="slotProps">
            <q-td class="text-center">
                <ActionButtonDropdown :data="slotProps.row" :lists="actionLists" @itemClick="onItemClick" />
            </q-td>
        </template>
    </Table>
    <PermissionGroupFormModal v-model="formModal" :action="formAction" :data="rowData"
        @closeModal="closePermissionGroupFormModal" />
</template>
<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import PermissionGroupFormModal from "@/Components/PermissionGroupFormModal.vue";

const rowData = ref(null);
const formAction = ref(null);
const formModal = ref(false);
const tableData = ref(null);
const $q = useQuasar();

const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'name', label: 'Name', field: 'name', sortable: true, align: 'left' },
    { name: 'actions', label: 'Actions', field: 'actions', sortable: false, align: 'left', hidden: true },
]

const url = '/table/permissiongroups';

const openPermissionGroupFormModal = (action, data = null) => {
    rowData.value = data;
    formAction.value = action;
    formModal.value = true;
}

const closePermissionGroupFormModal = () => {
    formModal.value = false;
    tableData.value.refresh();
}

const actionLists = [
    { label: 'Edit', value: 'edit' },
    { label: 'Delete', value: 'delete' },
]
const onItemClick = (action, data) => {
    if (action == "edit") {
        openPermissionGroupFormModal('update', data);
    } else if (action == 'delete') {
        deletePermissionGroup(data);
    }
}

const deletePermissionGroup = (data) => {
    $q.dialog({
        title: 'Are you sure?',
        message: `Are you srue you want to delete ${data.name}?`
    }).onOk(() => {
        axios.delete(route('permissiongroups.destroy', { permissiongroup: data.id }))
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

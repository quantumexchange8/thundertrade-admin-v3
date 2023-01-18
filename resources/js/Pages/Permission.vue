<template>

    <Head title="Permission" />
    <Table :columns="columns" :url="url" ref="tableData" :search="search">
        <template #top-left1>
            <q-btn outline label="create" @click="openPermissionFormModal" />
        </template>
        <template #body-cell-actions="slotProps">
            <q-td class="text-center">
                <ActionButtonDropdown :data="slotProps.row" :lists="actionLists" @itemClick="onItemClick" />
            </q-td>
        </template>
    </Table>

    <PermissionFormModal v-model="formModal" :action="formAction" :data="rowData"
        @closeModal="closePermissionFormModal" />
</template>
<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import PermissionFormModal from "@/Components/PermissionFormModal.vue";


const search = ref({
    mode: 'or',
    'group.name': '',
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
const rowData = ref(null);
const formAction = ref(null);
const formModal = ref(false);
const tableData = ref(null);
const $q = useQuasar();

const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'name', label: 'Name', field: 'name', sortable: true, align: 'left' },
    { name: 'permissionGroup.name', label: 'Permission Group Name', field: row => row.permission_group.name, sortable: true, align: 'left' },
    { name: 'actions', label: 'Actions', field: 'actions', sortable: false, align: 'left', hidden: true },
]

let url = '/table/permissions';

const openPermissionFormModal = (action, data = null) => {
    rowData.value = data;
    formAction.value = action;
    formModal.value = true;
}

const closePermissionFormModal = () => {
    formModal.value = false;
    tableData.value.refresh();
}
const actionLists = [
    { label: 'Edit', value: 'edit' },
    { label: 'Delete', value: 'delete' },
]
const onItemClick = (action, data) => {
    if (action == "edit") {
        openPermissionFormModal('update', data);
    } else if (action == 'delete') {
        deletePermission(data);
    }
}

const deletePermission = (data) => {
    $q.dialog({
        title: 'Are you sure?',
        message: `Are you srue you want to delete ${data.name}?`
    }).onOk(() => {
        axios.delete(route('permissions.destroy', { permission: data.id }))
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

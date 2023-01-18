<template>

    <Head title="Ranking" />
    <Table :columns="columns" :url="url" ref="tableData">
        <template #top-left1>
            <q-btn outline label="create" @click="openRankingFormModal" />
        </template>
        <template #body-cell-actions="slotProps">
            <q-td class="text-center">
                <ActionButtonDropdown :data="slotProps.row" :lists="actionLists" @itemClick="onItemClick" />
            </q-td>
        </template>


    </Table>

    <RankingFormModal v-model="formModal" :action="formAction" :data="rowData" @closeModal="closeRankingFormModal" />
</template>
<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import RankingFormModal from "@/Components/RankingFormModal.vue"
import axios from "axios";

const rowData = ref(null);
const formAction = ref(null);
const formModal = ref(false);
const tableData = ref(null);
const $q = useQuasar();

const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'level', label: 'Level', field: 'level', sortable: true, align: 'left' },
    { name: 'amount', label: 'Amount', field: 'amount', sortable: true, align: 'left' },
    { name: 'deposit', label: 'Deposit Fee', field: 'deposit', sortable: true, align: 'left' },
    { name: 'withdrawal', label: 'Withdrawal Fee', field: 'withdrawal', sortable: true, align: 'left' },
    { name: 'actions', label: 'Actions', field: 'actions', sortable: false, align: 'left', hidden: true },
]

const url = '/table/rankings';
const openRankingFormModal = (action, data = null) => {
    rowData.value = data;
    formAction.value = action;
    formModal.value = true;
}

const closeRankingFormModal = () => {
    formModal.value = false;
    tableData.value.refresh();
}
const actionLists = [
    { label: 'Edit', value: 'edit' },
    { label: 'Delete', value: 'delete' },
]
const onItemClick = (action, data) => {
    if (action == "edit") {
        openRankingFormModal('update', data);
    } else if (action == 'delete') {
        deleteRanking(data);
    }
}

const deleteRanking = (data) => {
    $q.dialog({
        title: 'Are you sure?',
        message: `Are you srue you want to delete ${data.level}?`
    }).onOk(() => {
        axios.delete(route('rankings.destroy', { ranking: data.id }))
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

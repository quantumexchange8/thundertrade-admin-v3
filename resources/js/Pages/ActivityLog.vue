<template>

    <Head title="Activity Log" />
    <Table :columns="columns" :url="url" ref="tableData">
        <template #body-cell-properties="props">
            <q-td class="tw-space-x-1" :props="props">
                {{ props.value }}
            </q-td>
        </template>
    </Table>
</template>
<script setup>
import { ref } from "vue";

const tableData = ref(null);

const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'log_name', label: 'Log Name', field: 'log_name', sortable: true, align: 'left' },
    { name: 'description', label: 'Description', field: 'description', sortable: true, align: 'left' },
    { name: 'event', label: 'Event', field: 'event', sortable: true, align: 'left' },
    { name: 'subject_id', label: 'Subject Name', field: row => subject(row), sortable: true, align: 'left' },
    { name: 'causer_id', label: 'Causer Name', field: row => row.causer?.name, sortable: true, align: 'left' },
    { name: 'properties', label: 'Properties', field: 'properties', sortable: true, align: 'left' }
]

const url = '/table/activitylogs';

const subject = (row) => {
    if (!row.subject_type) return null;
    let modelPath = row.subject_type.split("\\");
    let model = modelPath.pop();
    let d = `(${model})`;
    if (model === 'UserTransaction') {
        d += row.subject?.transaction_number;
    } else if (model === 'Ranking') {
        d += row.subject?.level;
    } else {
        d += row.subject?.name
    }
    return d;
}

</script>

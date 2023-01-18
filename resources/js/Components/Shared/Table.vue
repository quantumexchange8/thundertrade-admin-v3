<template>
    <q-table v-model:pagination="pagination" :columns="columns" :rows="data" binary-state-sort :loading="loading"
        :filter="filter" @request="onRequest">
        <template v-for="(_, slot) of $slots" v-slot:[slot]="scope">
            <slot :name="slot" v-bind="{ ...scope }" />
        </template>
        <template #top-left>
            <div class="tw-space-x-2">
                <q-btn-dropdown outline label="Export" v-if="showExport">
                    <q-list>
                        <q-item v-for="(opt, index) in exportOptions" :key="index" clickable
                            @click="exportFunc(pagination, exportOptionsList[opt].filetype)">
                            <q-item-section>
                                <q-item-label>
                                    {{ exportOptionsList[opt].label }}
                                </q-item-label>
                            </q-item-section>
                        </q-item>
                    </q-list>
                </q-btn-dropdown>
                <slot name="top-left1"></slot>

            </div>
        </template>
        <template #top-right="slotProps">
            <div class="tw-space-x-2">
                <q-input borderless dense v-model="filter" debounce="1000" placeholder="Search" v-if="showInlineSearch">
                    <template #append>
                        <q-icon name="search" />
                    </template>
                </q-input>
                <q-btn outline icon="refresh" @click="refresh()" :loading="loading" v-if="showRefresh" />
                <q-btn flat round dense :icon="slotProps.inFullscreen ? 'fullscreen_exit' : 'fullscreen'"
                    @click="slotProps.toggleFullscreen" class="q-ml-md" v-if="showFullScreen" />
            </div>
        </template>

        <template #loading>
            <q-inner-loading showing label="Please Wait" />
        </template>
    </q-table>
</template>
<script setup>
import { ref, onMounted, computed } from "vue"
import axios from "axios"
import print from 'print-js'
import { useQuasar, exportFile } from "quasar";


const $q = useQuasar();
const filter = ref(null);
const dataRes = ref(null);
const data = ref([])
const props = defineProps({
    columns: { type: Array, required: true },
    url: { type: String, required: true },
    search: Object,
    showRefresh: Boolean,
    showExport: { type: Boolean, default: true },
    showFullScreen: Boolean,
    showInlineSearch: Boolean,
    exportOptions: { type: Array, default: ['CSV', 'EXCEL', 'PDF', 'PRINT'] }
})

const exportOptionsList = {
    CSV: { label: 'CSV', filetype: 'csv' },
    EXCEL: { label: 'EXCEL', filetype: 'xlsx' },
    PDF: { label: 'PDF', filetype: 'pdf' },
    PRINT: { label: 'PRINT', filetype: 'pdfPrint' },
}

const loading = ref(true);
const headings = computed(() => props.columns?.filter(column => !column.hidden)?.map(({ name, label }) => { return { name, label } }))
const pagination = ref({
    page: 1,
    rowsPerPage: 10,
    rowsNumber: 0,
    sortBy: null,
    descending: false
})

const fetchData = async (page, rowsPerPage, sortBy, descending, headings, search) => {
    if (props.url) {
        loading.value = true
        try {
            const response = await axios.get(props.url, {
                params: {
                    page, rowsPerPage, sortBy, descending, headings, search, filter: filter.value,
                }
            });
            dataRes.value = response.data;
            const { data: records, current_page, per_page, total } = response.data;
            data.value = records;
            pagination.value = {
                page: current_page,
                rowsPerPage: per_page,
                rowsNumber: total,
                sortBy,
                descending,


            }
        } catch (error) {
            $q.notify({ 'type': 'negative', 'message': 'Server error' });
            console.error(error);
        } finally {
            return loading.value = false;
        }
    }
}

const onRequest = (options) => {
    const { page, rowsPerPage, sortBy, descending } = options.pagination;
    fetchData(page, rowsPerPage, sortBy, descending, headings.value, props.search)
}


const exportFunc = (pagination, type) => {
    loading.value = true
    axios.get(props.url, {
        responseType: 'arraybuffer',
        params: {
            sortBy: pagination.sortBy,
            descending: pagination.descending,
            export: true,
            type: type,
            headings: headings.value,
            search: props.search,
            filter: filter.value
        }
    }).then((response) => {
        let fileName = 'table'
        const fileBlob = new Blob([response.data])

        if (type === 'pdfPrint') {
            const url = window.URL.createObjectURL(fileBlob)
            printJS(url)
        } else {
            exportFile(fileName.concat(".", type), fileBlob)
        }

    }).finally(() => loading.value = false)
}
const refresh = () => {
    const { page, rowsPerPage, sortBy, descending } = pagination.value;
    fetchData(page, rowsPerPage, sortBy, descending, headings.value, props.search)
}

defineExpose({
    refresh, dataRes
})


onMounted(() => {
    refresh();
})
</script>

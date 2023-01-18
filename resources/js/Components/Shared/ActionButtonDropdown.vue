<template>
    <q-btn-dropdown label="Action">
        <q-list>
            <q-item clickable v-close-popup v-for="item in lists" @click="onItemClick(item.value, data)">
                <q-item-section>
                    <q-item-label>{{ labelDisplay(item.label, data) }}</q-item-label>
                </q-item-section>
            </q-item>
        </q-list>
    </q-btn-dropdown>
</template>
<script setup>
const props = defineProps({ data: Object, lists: Array });
const emit = defineEmits(['itemClick'])
const onItemClick = (action, data) => {
    emit('itemClick', action, data);
}
const labelDisplay = (label, row) => (typeof label == 'function') ? label(row) : label

</script>
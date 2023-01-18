<template>
    <q-list class="tw-py-3" bordered separator>
        <q-item>
            <q-item-section>
                <Link :href="route('merchants.index')">
                <q-btn class="full-width" push rounded color="yellow" text-color="black" label="Merchant" />
                </Link>
            </q-item-section>
        </q-item>
        <q-item>
            <q-item-section>
                <Link :href="route('transaction.index')">
                <q-btn class="full-width" push rounded color="yellow" text-color="black" label="Merchant Transaction" />
                </Link>
            </q-item-section>
        </q-item>
        <q-item>
            <q-item-section>
                <Link :href="route('ranking')">
                <q-btn class="full-width" push rounded color="yellow" text-color="black" label="Ranking" />
                </Link>
            </q-item-section>
        </q-item>
        <q-item>
            <q-item-section>
                <Link :href="route('role')">
                <q-btn class="full-width" push rounded color="yellow" text-color="black" label="Role" />
                </Link>
            </q-item-section>
        </q-item>
        <q-item>
            <q-item-section>
                <Link :href="route('user')">
                <q-btn class="full-width" push rounded color="yellow" text-color="black" label="User" />
                </Link>
            </q-item-section>
        </q-item>
        <q-item>
            <q-item-section>
                <Link :href="route('userTransaction')">
                <q-btn class="full-width" push rounded color="yellow" text-color="black" label="User Transaction" />
                </Link>
            </q-item-section>
        </q-item>
        <q-item>
            <q-item-section>
                <Link :href="route('permissionGroup')">
                <q-btn class="full-width" push rounded color="yellow" text-color="black" label="Permission Group" />
                </Link>
            </q-item-section>
        </q-item>
        <q-item>
            <q-item-section>
                <Link :href="route('permission')">
                <q-btn class="full-width" push rounded color="yellow" text-color="black" label="Permission" />
                </Link>
            </q-item-section>
        </q-item>
        <q-item>
            <q-item-section>
                <Link :href="route('activityLog')">
                <q-btn class="full-width" push rounded color="yellow" text-color="black" label="Activity Log" />
                </Link>
            </q-item-section>
        </q-item>
    </q-list>
</template>

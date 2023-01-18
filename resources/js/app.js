import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { Quasar, Notify, Dialog } from 'quasar';
import '@quasar/extras/material-icons/material-icons.css';
import 'quasar/src/css/index.sass';
import Layout from "./Layouts/Layout.vue";


import QSelectWithValidation from "@/Components/Shared/QSelectWithValidation.vue";
import QInputWithValidation from "@/Components/Shared/QInputWithValidation.vue";
import QFileWithValidation from "@/Components/Shared/QFileWithValidation.vue";
import QCheckboxWithValidation from "@/Components/Shared/QCheckboxWithValidation.vue";
import ActionButtonDropdown from "@/Components/Shared/ActionButtonDropdown.vue";
import Table from "@/Components/Shared/Table.vue";
import Modal from "@/Components/Shared/Modal.vue";
import Empty from "@/Components/Shared/Empty.vue";
const appName = 'ThunderTrade';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const page = resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'))
        page.then(module => {
            if (!name.startsWith('Auth')) {
                module.default.layout = Layout;
            }
        })
        return page;
    },
    setup({ el, app, props, plugin }) {
        return createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(Quasar, {
                plugins: {
                    Notify,
                    Dialog
                },
            })
            .use(ZiggyVue, Ziggy)
            .component('Head', Head)
            .component('Link', Link)
            .component("QSelectWithValidation", QSelectWithValidation)
            .component("QInputWithValidation", QInputWithValidation)
            .component("QCheckboxWithValidation", QCheckboxWithValidation)
            .component("QFileWithValidation", QFileWithValidation)
            .component("Table", Table)
            .component("Modal", Modal)
            .component("ActionButtonDropdown", ActionButtonDropdown)
            .component("Empty", Empty)
            .mount(el);
    },
});

InertiaProgress.init({ color: '#FEDC00' });

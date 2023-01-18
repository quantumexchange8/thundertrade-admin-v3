import {
    QExpansionItem,
    QList,
    QItem,
    QItemSection,
    QIcon,
    QBadge,
    Ripple,
    QBtn,
} from "quasar";
import { Link, usePage } from "@inertiajs/inertia-vue3";
import { computed, h, ref, watch, withDirectives } from "vue";

///no active class
///route path not set (havent find way to get current route path not route name)
export default {
    name: "AppMenu",
    props: ["menu"],
    setup(props) {
        const modules = [];

        const routePath = ref(usePage().url);
        const rootRef = ref(null);
        const isActive = (path) => route(path).includes(routePath.value);
        const isActiveDrawer = (path) => route().current(`${path}.*`);
        function getDrawerMenu(menu, path, level) {
            if (menu.hidden) return;

            if (menu.module && !modules.includes(menu.module)) return;

            if (menu.children !== void 0) {
                return h(
                    QExpansionItem,
                    {
                        group: `app-navbar-${level}`, //accordian mode
                        class: "non-selectable",
                        //key: `${menu.name}-${path}`,
                        key: `${menu.name}`,
                        label: menu.name,
                        dense: false,
                        icon: menu.icon,
                        defaultOpened: menu.opened || isActiveDrawer(menu.path),
                        expandSeparator: true,
                        switchToggleSide: level > 0,
                        headerClass: isActiveDrawer(menu.path)
                            ? "text-primary"
                            : "",
                        //denseToggle: level > 0,
                    },
                    () =>
                        menu.children.map((item) =>
                            getDrawerMenu(item, item.path, level + 1)
                        )
                );
            }

            const props = {
                key: path,
                class: "non-selectable",
                active: menu.external ? false : isActive(menu.path),
                // dense: level > 0,
                insetLevel: level > 1 ? 1.2 : level,
            };

            const child = [];

            menu.icon !== void 0 &&
                child.push(
                    h(
                        QItemSection,
                        {
                            avatar: true,
                        },
                        () => h(QIcon, { name: menu.icon })
                    )
                );

            child.push(h(QItemSection, () => h(QBtn, {
                label: menu.name,
                push: true,
                rounded: true,
                color: 'yellow',
                textColor: isActive(menu.path) ? 'primary' : 'black'
            })));

            menu.badge !== void 0 &&
                child.push(
                    h(
                        QItemSection,
                        {
                            side: true,
                        },
                        () =>
                            h(QBadge, {
                                label: menu.badge,
                                color: "brand-primary",
                            })
                    )
                );

            if (menu.external) {
                return h(
                    "a",
                    {
                        target: "_blank",
                        href: menu.path,
                    },
                    [
                        withDirectives(
                            h(QItem, props, () => child),
                            [[Ripple]]
                        ),
                    ]
                );
            } else {
                return h(
                    Link,
                    {
                        method: menu.pathMethod ? menu.pathMethod : "GET",
                        href: route(menu.path),
                    },
                    () =>
                        withDirectives(
                            h(QItem, props, () => child),
                            [[Ripple]]
                        )
                );
            }
        }

        return () =>
            h(QList, { ref: rootRef, class: "app-menu", dense: false }, () =>
                props.menu.map((item) => getDrawerMenu(item, item.path, 0))
            );
    },
};

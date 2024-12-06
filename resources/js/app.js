import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Layouts
import AuthenticatedHeaderLayout from './Layouts/AuthenticatedHeaderLayout.vue';
import GuestHeaderLayout from './Layouts/GuestHeaderLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'LLYMAR';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ).then(({ default: page }) => {
            if (name.startsWith('App')) {
                page.layout = AuthenticatedHeaderLayout;
            } else {
                page.layout = GuestHeaderLayout;
            }
            return page;
        }),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

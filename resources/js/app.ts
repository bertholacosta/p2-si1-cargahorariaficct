import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import Tooltip from 'primevue/tooltip';
import ToastService from 'primevue/toastservice';
import 'primeicons/primeicons.css';

// Importar configuración de axios
import './axios';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Configurar Inertia para enviar la fecha del cliente en cada petición
router.on('before', (event) => {
    // Agregar la fecha/hora del cliente a los headers
    event.detail.visit.headers = {
        ...event.detail.visit.headers,
        'X-Client-Time': new Date().toISOString(),
    };
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(PrimeVue, {
                theme: {
                    preset: Aura
                }
            })
            .use(ToastService)
            .directive('tooltip', Tooltip)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

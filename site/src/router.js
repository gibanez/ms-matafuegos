import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('@/views/HomeView.vue'),
        meta: {
            title: 'MS Matafuegos | Recarga, venta y servicio a domicilio en Tucumán',
            description:
                'Recarga y venta de matafuegos para tu negocio, casa, vehículo, consorcio, galpón o escuela. Presupuesto sin cargo y servicio a domicilio en Tucumán.',
        },
    },
    {
        path: '/productos',
        name: 'products',
        component: () => import('@/views/ProductsView.vue'),
        meta: {
            title: 'Productos y servicios | MS Matafuegos',
            description:
                'Matafuegos, recargas, accesorios y elementos de seguridad. Consultá disponibilidad y precios.',
        },
    },
    {
        path: '/productos/:slug',
        name: 'product',
        component: () => import('@/views/ProductDetailView.vue'),
        meta: {
            title: 'Producto | MS Matafuegos',
            description: 'Conocé el detalle del producto y consultá por WhatsApp.',
        },
    },
    {
        path: '/consultas',
        name: 'inquiries',
        component: () => import('@/views/InquiryView.vue'),
        meta: {
            title: 'Pedí tu presupuesto | MS Matafuegos',
            description:
                'Contanos qué necesitás y te respondemos sin cargo. Consultas por WhatsApp, recargas y servicio a domicilio.',
        },
    },
    {
        path: '/contacto',
        name: 'contact',
        component: () => import('@/views/ContactView.vue'),
        meta: {
            title: 'Contacto y ubicación | MS Matafuegos',
            description:
                'Encontranos en Av. Juan B. Justo 2665, San Miguel de Tucumán. Horario comercial de lunes a sábados.',
        },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/views/NotFoundView.vue'),
        meta: {
            title: 'Página no encontrada | MS Matafuegos',
        },
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }

        if (to.hash) {
            return {
                el: to.hash,
                behavior: 'smooth',
                top: 96,
            };
        }

        return { top: 0, behavior: 'smooth' };
    },
});

router.afterEach((to) => {
    document.title = to.meta.title || 'MS Matafuegos';

    const description = document.querySelector('meta[name="description"]');

    if (description && to.meta.description) {
        description.setAttribute('content', to.meta.description);
    }
});

export default router;

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Message from 'primevue/message';
import Skeleton from 'primevue/skeleton';
import Tag from 'primevue/tag';
import { api } from '../api';
import { buildWeek, formatLongDate } from '../maintenance';
import { formatDate, money, statusLabel, statusSeverity } from '../utils';
import MaintenanceDayDialog from './MaintenanceDayDialog.vue';

const emit = defineEmits(['navigate', 'create']);

const loading = ref(true);
const error = ref('');
const data = reactive({
    products: [],
    categories: [],
    providers: [],
    customers: [],
    quotes: [],
    invoices: [],
    inquiries: [],
});

const endpoints = [
    ['products', '/api/products'],
    ['categories', '/api/categories'],
    ['providers', '/api/providers'],
    ['customers', '/api/customers'],
    ['quotes', '/api/quotes'],
    ['invoices', '/api/invoices'],
    ['inquiries', '/api/inquiries'],
];

const maintenance = reactive({
    from: '',
    to: '',
    today: '',
    items: [],
    overdue: [],
});
const maintenanceDialog = reactive({
    visible: false,
    title: '',
    ids: [],
});

const weekDays = computed(() => buildWeek(maintenance.from));

const maintenanceByDate = computed(() => {
    const map = {};

    maintenance.items.forEach((item) => {
        if (!map[item.notifyDate]) {
            map[item.notifyDate] = [];
        }

        map[item.notifyDate].push(item);
    });

    return map;
});

const maintenanceKnown = computed(() => {
    const map = new Map();

    [...maintenance.items, ...maintenance.overdue].forEach((item) => map.set(item.id, item));

    return [...map.values()];
});

const maintenanceDialogItems = computed(() => maintenanceDialog.ids
    .map((id) => maintenanceKnown.value.find((item) => item.id === id))
    .filter(Boolean));

const maintenancePending = computed(() => maintenance.items
    .filter((item) => item.status !== 'notified').length);

function maintenanceCount(date) {
    return (maintenanceByDate.value[date] || []).filter((item) => item.status !== 'notified').length;
}

function maintenanceOverdueDay(date) {
    return date < maintenance.today && maintenanceCount(date) > 0;
}

function openMaintenanceDay(date) {
    maintenanceDialog.ids = (maintenanceByDate.value[date] || []).map((item) => item.id);
    maintenanceDialog.title = `Avisos del ${formatLongDate(date)}`;
    maintenanceDialog.visible = true;
}

function openMaintenanceOverdue() {
    maintenanceDialog.ids = maintenance.overdue.map((item) => item.id);
    maintenanceDialog.title = 'Avisos atrasados';
    maintenanceDialog.visible = true;
}

async function loadMaintenance() {
    try {
        const response = await api('/api/maintenance/calendar');

        maintenance.from = response.from;
        maintenance.to = response.to;
        maintenance.today = response.today;
        maintenance.items = response.items || [];
        maintenance.overdue = response.overdue || [];
    } catch {
        maintenance.items = [];
        maintenance.overdue = [];
    }
}

const products = computed(() => data.products);
const lowStock = computed(() => products.value.filter((product) => product.lowStock));
const recentQuotes = computed(() => [...data.quotes]
    .sort((a, b) => String(b.createdAt || b.issueDate).localeCompare(String(a.createdAt || a.issueDate)))
    .slice(0, 5));
const recentInvoices = computed(() => [...data.invoices]
    .sort((a, b) => String(b.createdAt || b.issueDate).localeCompare(String(a.createdAt || a.issueDate)))
    .slice(0, 5));

const stats = computed(() => [
    {
        label: 'Productos',
        value: data.products.length,
        detail: `${data.products.filter((product) => product.status === 'active').length} activos`,
        icon: 'pi pi-box',
        tone: 'blue',
        view: 'products',
    },
    {
        label: 'Stock bajo',
        value: lowStock.value.length,
        detail: lowStock.value.length ? 'Requieren reposición' : 'Todo en orden',
        icon: 'pi pi-exclamation-triangle',
        tone: lowStock.value.length ? 'amber' : 'green',
        view: 'products',
    },
    {
        label: 'Clientes',
        value: data.customers.length,
        detail: `${data.customers.filter((customer) => customer.status === 'active').length} activos`,
        icon: 'pi pi-users',
        tone: 'violet',
        view: 'customers',
    },
    {
        label: 'Presupuestos',
        value: data.quotes.length,
        detail: `${data.quotes.filter((quote) => quote.status === 'accepted').length} aceptados`,
        icon: 'pi pi-file-edit',
        tone: 'cyan',
        view: 'quotes',
    },
    {
        label: 'Facturas',
        value: data.invoices.length,
        detail: `${data.invoices.filter((invoice) => invoice.status === 'paid').length} pagadas`,
        icon: 'pi pi-file',
        tone: 'green',
        view: 'invoices',
    },
    {
        label: 'Consultas',
        value: data.inquiries.length,
        detail: 'Mensajes recibidos',
        icon: 'pi pi-inbox',
        tone: 'rose',
        view: 'inquiries',
    },
]);

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const results = await Promise.allSettled(endpoints.map(async ([key, path]) => {
            const response = await api(path);
            return [key, response[key] || []];
        }));

        results.forEach((result) => {
            if (result.status === 'fulfilled') {
                data[result.value[0]] = result.value[1];
            }
        });

        if (results.every((result) => result.status === 'rejected')) {
            error.value = 'No se pudo cargar el panel. Revisá la conexión con la API.';
        }

        await loadMaintenance();
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <section class="dashboard">
        <div class="welcome-banner">
            <div>
                <span class="eyebrow">Resumen general</span>
                <h2>Todo el negocio, en una mirada.</h2>
                <p>Controlá el catálogo, el stock y la facturación desde el panel.</p>
            </div>
            <div class="welcome-actions">
                <Button label="Nuevo producto" icon="pi pi-plus" @click="emit('create', 'products')" />
                <Button
                    label="Nuevo presupuesto"
                    icon="pi pi-file-edit"
                    severity="secondary"
                    outlined
                    @click="emit('create', 'quotes')"
                />
            </div>
        </div>

        <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>

        <div class="stats-grid">
            <button
                v-for="stat in stats"
                :key="stat.label"
                type="button"
                class="stat-card"
                @click="emit('navigate', stat.view)"
            >
                <span class="stat-icon" :class="`tone-${stat.tone}`"><i :class="stat.icon"></i></span>
                <span class="stat-copy">
                    <small>{{ stat.label }}</small>
                    <strong v-if="!loading">{{ stat.value }}</strong>
                    <Skeleton v-else width="3.5rem" height="1.9rem" />
                    <span>{{ stat.detail }}</span>
                </span>
                <i class="pi pi-arrow-up-right stat-arrow"></i>
            </button>
        </div>

        <Card class="dashboard-card maintenance-week-card">
            <template #title>
                <div class="card-heading">
                    <div>
                        <span class="eyebrow">Mantenimientos</span>
                        <h3>Avisos de la semana</h3>
                    </div>
                    <Button
                        label="Ver agenda"
                        text
                        size="small"
                        icon="pi pi-arrow-right"
                        icon-pos="right"
                        @click="emit('navigate', 'maintenance')"
                    />
                </div>
            </template>
            <template #content>
                <div v-if="loading" class="list-skeleton">
                    <Skeleton height="4.5rem" />
                </div>

                <template v-else>
                    <div class="week-strip">
                        <button
                            v-for="day in weekDays"
                            :key="day.date"
                            type="button"
                            class="week-day"
                            :class="{
                                today: day.date === maintenance.today,
                                overdue: maintenanceOverdueDay(day.date),
                                filled: maintenanceCount(day.date) > 0,
                            }"
                            @click="openMaintenanceDay(day.date)"
                        >
                            <small>{{ day.weekday }}</small>
                            <strong>{{ day.day }}</strong>
                            <span
                                v-if="maintenanceCount(day.date)"
                                class="week-badge"
                                :class="{ danger: maintenanceOverdueDay(day.date) }"
                            >{{ maintenanceCount(day.date) }}</span>
                            <span v-else class="week-dot"></span>
                        </button>
                    </div>

                    <div v-if="maintenance.overdue.length" class="week-overdue">
                        <span>
                            <i class="pi pi-exclamation-triangle"></i>
                            {{ maintenance.overdue.length }}
                            {{ maintenance.overdue.length === 1 ? 'aviso atrasado' : 'avisos atrasados' }}
                        </span>
                        <Button label="Ver atrasados" text size="small" @click="openMaintenanceOverdue" />
                    </div>
                    <p v-else-if="!maintenancePending" class="week-hint">
                        No hay avisos programados para esta semana.
                    </p>
                    <p v-else class="week-hint">
                        {{ maintenancePending }}
                        {{ maintenancePending === 1 ? 'cliente' : 'clientes' }} para avisar esta semana.
                    </p>
                </template>
            </template>
        </Card>

        <MaintenanceDayDialog
            v-model:visible="maintenanceDialog.visible"
            :title="maintenanceDialog.title"
            :items="maintenanceDialogItems"
            @changed="loadMaintenance"
        />

        <div class="dashboard-columns">
            <Card class="dashboard-card">
                <template #title>
                    <div class="card-heading">
                        <div>
                            <span class="eyebrow">Inventario</span>
                            <h3>Stock bajo</h3>
                        </div>
                        <Button
                            label="Ver productos"
                            text
                            size="small"
                            icon="pi pi-arrow-right"
                            icon-pos="right"
                            @click="emit('navigate', 'products')"
                        />
                    </div>
                </template>
                <template #content>
                    <div v-if="loading" class="list-skeleton">
                        <Skeleton v-for="index in 4" :key="index" height="3rem" />
                    </div>
                    <div v-else-if="lowStock.length" class="low-stock-list">
                        <button
                            v-for="product in lowStock.slice(0, 6)"
                            :key="product.id"
                            type="button"
                            class="low-stock-item"
                            @click="emit('navigate', 'products')"
                        >
                            <span class="low-stock-icon"><i class="pi pi-box"></i></span>
                            <span class="low-stock-copy">
                                <strong>{{ product.name }}</strong>
                                <small>{{ product.sku || 'Sin SKU' }}</small>
                            </span>
                            <span class="stock-pill danger">{{ product.stock }} / {{ product.minStock }}</span>
                        </button>
                    </div>
                    <div v-else class="dashboard-empty">
                        <i class="pi pi-check-circle"></i>
                        <strong>Sin alertas de stock</strong>
                        <span>Todos los productos están por encima del mínimo.</span>
                    </div>
                </template>
            </Card>

            <Card class="dashboard-card">
                <template #title>
                    <div class="card-heading">
                        <div>
                            <span class="eyebrow">Ventas</span>
                            <h3>Últimos presupuestos</h3>
                        </div>
                        <Button
                            label="Ver todos"
                            text
                            size="small"
                            icon="pi pi-arrow-right"
                            icon-pos="right"
                            @click="emit('navigate', 'quotes')"
                        />
                    </div>
                </template>
                <template #content>
                    <div v-if="loading" class="list-skeleton">
                        <Skeleton v-for="index in 4" :key="index" height="3rem" />
                    </div>
                    <div v-else-if="recentQuotes.length" class="recent-list">
                        <button
                            v-for="quote in recentQuotes"
                            :key="quote.id"
                            type="button"
                            class="recent-item"
                            @click="emit('navigate', 'quotes')"
                        >
                            <span class="recent-copy">
                                <strong>{{ quote.number }}</strong>
                                <small>{{ quote.customer?.name || 'Cliente eliminado' }} · {{ formatDate(quote.issueDate) }}</small>
                            </span>
                            <span class="recent-total">{{ money(quote.total) }}</span>
                            <Tag :value="statusLabel(quote.status)" :severity="statusSeverity(quote.status)" />
                        </button>
                    </div>
                    <div v-else class="dashboard-empty">
                        <i class="pi pi-file-edit"></i>
                        <strong>Todavía no hay presupuestos</strong>
                        <span>Creá el primero desde el módulo de presupuestos.</span>
                    </div>
                </template>
            </Card>
        </div>

        <Card class="dashboard-card">
            <template #title>
                <div class="card-heading">
                    <div>
                        <span class="eyebrow">Facturación</span>
                        <h3>Últimas facturas</h3>
                    </div>
                    <Button
                        label="Ver todas"
                        text
                        size="small"
                        icon="pi pi-arrow-right"
                        icon-pos="right"
                        @click="emit('navigate', 'invoices')"
                    />
                </div>
            </template>
            <template #content>
                <div v-if="loading" class="list-skeleton">
                    <Skeleton v-for="index in 4" :key="index" height="3rem" />
                </div>
                <div v-else-if="recentInvoices.length" class="recent-list">
                    <button
                        v-for="invoice in recentInvoices"
                        :key="invoice.id"
                        type="button"
                        class="recent-item"
                        @click="emit('navigate', 'invoices')"
                    >
                        <span class="recent-copy">
                            <strong>{{ invoice.number }}</strong>
                            <small>{{ invoice.customer?.name || 'Cliente eliminado' }} · {{ formatDate(invoice.issueDate) }}</small>
                        </span>
                        <span class="recent-total">{{ money(invoice.total) }}</span>
                        <Tag :value="statusLabel(invoice.status)" :severity="statusSeverity(invoice.status)" />
                    </button>
                </div>
                <div v-else class="dashboard-empty">
                    <i class="pi pi-file"></i>
                    <strong>Todavía no hay facturas</strong>
                    <span>Podés crear una desde cero o a partir de un presupuesto.</span>
                </div>
            </template>
        </Card>
    </section>
</template>

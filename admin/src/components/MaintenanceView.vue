<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import Button from 'primevue/button';
import Message from 'primevue/message';
import Skeleton from 'primevue/skeleton';
import Tag from 'primevue/tag';
import { api } from '../api';
import { formatDate } from '../utils';
import {
    WEEKDAYS,
    buildMonthGrid,
    formatLongDate,
    maintenanceStatusLabel,
    maintenanceStatusSeverity,
    monthLabel,
} from '../maintenance';
import MaintenanceDayDialog from './MaintenanceDayDialog.vue';

const cursor = ref(new Date());
const loading = ref(true);
const error = ref('');
const data = reactive({
    from: '',
    to: '',
    today: '',
    items: [],
    overdue: [],
});
const selectedDay = ref('');
const dialog = reactive({
    visible: false,
    title: '',
    ids: [],
});

const grid = computed(() => buildMonthGrid(cursor.value.getFullYear(), cursor.value.getMonth()));
const monthPrefix = computed(() => {
    const month = String(cursor.value.getMonth() + 1).padStart(2, '0');

    return `${cursor.value.getFullYear()}-${month}`;
});

const itemsByDate = computed(() => {
    const map = {};

    data.items.forEach((item) => {
        if (!map[item.notifyDate]) {
            map[item.notifyDate] = [];
        }

        map[item.notifyDate].push(item);
    });

    return map;
});

const knownItems = computed(() => {
    const map = new Map();

    [...data.items, ...data.overdue].forEach((item) => map.set(item.id, item));

    return [...map.values()];
});

const dialogItems = computed(() => dialog.ids
    .map((id) => knownItems.value.find((item) => item.id === id))
    .filter(Boolean));

const selectedItems = computed(() => itemsByDate.value[selectedDay.value] || []);
const monthPending = computed(() => data.items.filter((item) => (
    item.status !== 'notified' && item.notifyDate.startsWith(monthPrefix.value)
)).length);

const monthPendingLabel = computed(() => (
    `${monthPending.value} ${monthPending.value === 1 ? 'pendiente' : 'pendientes'} este mes`
));

function pendingCount(date) {
    return (itemsByDate.value[date] || []).filter((item) => item.status !== 'notified').length;
}

function notifiedCount(date) {
    return (itemsByDate.value[date] || []).filter((item) => item.status === 'notified').length;
}

function isOverdue(date) {
    return date < data.today && pendingCount(date) > 0;
}

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const cells = grid.value;
        const from = cells[0].date;
        const to = cells[cells.length - 1].date;
        const response = await api(`/api/maintenance/calendar?from=${from}&to=${to}`);

        data.from = response.from;
        data.to = response.to;
        data.today = response.today;
        data.items = response.items || [];
        data.overdue = response.overdue || [];

        if (!selectedDay.value || selectedDay.value < data.from || selectedDay.value > data.to) {
            const todayInside = data.today >= data.from && data.today <= data.to;
            selectedDay.value = todayInside ? data.today : cells.find((cell) => cell.inMonth).date;
        }
    } catch (exception) {
        error.value = exception.message || 'No se pudo cargar la agenda de mantenimientos.';
    } finally {
        loading.value = false;
    }
}

function shiftMonth(delta) {
    cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() + delta, 1);
    selectedDay.value = '';
    load();
}

function goToday() {
    cursor.value = new Date();
    selectedDay.value = '';
    load();
}

function selectDay(date) {
    selectedDay.value = date;
}

function openDay(date) {
    dialog.ids = (itemsByDate.value[date] || []).map((item) => item.id);
    dialog.title = formatLongDate(date);
    dialog.visible = true;
}

function openItem(item) {
    dialog.ids = [item.id];
    dialog.title = item.customerName;
    dialog.visible = true;
}

function openOverdue() {
    dialog.ids = data.overdue.map((item) => item.id);
    dialog.title = 'Avisos atrasados';
    dialog.visible = true;
}

onMounted(load);
</script>

<template>
    <section class="maintenance-view">
        <div class="module-toolbar maintenance-toolbar">
            <div class="toolbar-left">
                <Button
                    icon="pi pi-chevron-left"
                    text
                    rounded
                    severity="secondary"
                    aria-label="Mes anterior"
                    @click="shiftMonth(-1)"
                />
                <strong class="maintenance-month">{{ monthLabel(cursor) }}</strong>
                <Button
                    icon="pi pi-chevron-right"
                    text
                    rounded
                    severity="secondary"
                    aria-label="Mes siguiente"
                    @click="shiftMonth(1)"
                />
                <Button label="Hoy" text size="small" @click="goToday" />
            </div>

            <div class="toolbar-right">
                <Tag :value="monthPendingLabel" severity="info" />
            </div>
        </div>

        <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>

        <div v-if="data.overdue.length" class="maintenance-overdue">
            <div class="maintenance-overdue-copy">
                <i class="pi pi-exclamation-triangle"></i>
                <div>
                    <strong>
                        {{ data.overdue.length }}
                        {{ data.overdue.length === 1 ? 'aviso atrasado' : 'avisos atrasados' }}
                    </strong>
                    <small>Clientes con mantenimiento vencido que todavía no fueron avisados.</small>
                </div>
            </div>
            <Button label="Ver atrasados" size="small" outlined @click="openOverdue" />
        </div>

        <div class="maintenance-layout">
            <div class="maintenance-calendar-card">
                <div v-if="loading" class="list-skeleton">
                    <Skeleton v-for="index in 6" :key="index" height="3.4rem" />
                </div>

                <div v-else class="calendar-grid">
                    <span v-for="label in WEEKDAYS" :key="label" class="calendar-weekday">{{ label }}</span>

                    <button
                        v-for="cell in grid"
                        :key="cell.date"
                        type="button"
                        class="calendar-day"
                        :class="{
                            outside: !cell.inMonth,
                            today: cell.date === data.today,
                            selected: cell.date === selectedDay,
                            overdue: isOverdue(cell.date),
                            filled: pendingCount(cell.date) > 0,
                        }"
                        @click="selectDay(cell.date)"
                    >
                        <span class="calendar-day-number">{{ cell.day }}</span>

                        <span
                            v-if="pendingCount(cell.date)"
                            class="calendar-badge"
                            :class="{ danger: isOverdue(cell.date) }"
                        >{{ pendingCount(cell.date) }}</span>
                        <span v-else-if="notifiedCount(cell.date)" class="calendar-check">
                            <i class="pi pi-check"></i>
                        </span>
                    </button>
                </div>
            </div>

            <div class="maintenance-day-card">
                <div class="maintenance-day-head">
                    <div>
                        <span class="eyebrow">Día seleccionado</span>
                        <h3>{{ selectedDay ? formatLongDate(selectedDay) : 'Elegí un día' }}</h3>
                    </div>
                    <Button
                        v-if="selectedItems.length"
                        label="Avisar"
                        size="small"
                        icon="pi pi-send"
                        @click="openDay(selectedDay)"
                    />
                </div>

                <div v-if="!selectedItems.length" class="dashboard-empty">
                    <i class="pi pi-calendar"></i>
                    <strong>Sin avisos</strong>
                    <span>No hay clientes para avisar este día.</span>
                </div>

                <div v-else class="maintenance-day-list">
                    <button
                        v-for="item in selectedItems"
                        :key="item.id"
                        type="button"
                        class="maintenance-day-item"
                        @click="openItem(item)"
                    >
                        <span class="maintenance-day-copy">
                            <strong>{{ item.customerName }}</strong>
                            <small>{{ item.productName }} · vence {{ formatDate(item.dueDate) }}</small>
                        </span>
                        <Tag
                            :value="maintenanceStatusLabel(item.status)"
                            :severity="maintenanceStatusSeverity(item.status)"
                        />
                        <i class="pi pi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <MaintenanceDayDialog
            v-model:visible="dialog.visible"
            :title="dialog.title"
            :items="dialogItems"
            @changed="load"
        />
    </section>
</template>

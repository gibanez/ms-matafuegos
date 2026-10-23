<script setup>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { api } from '../api';
import { formatDate } from '../utils';
import {
    maintenanceMessage,
    maintenanceStatusLabel,
    maintenanceStatusSeverity,
    whatsappHref,
} from '../maintenance';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Avisos',
    },
    items: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:visible', 'changed']);
const toast = useToast();
const busyId = ref('');
const error = ref('');

function waHref(item) {
    return whatsappHref(item.customerPhone, maintenanceMessage(item));
}

function daysText(item) {
    if (item.status === 'notified') {
        return item.notifiedAt ? `Avisado el ${formatDate(item.notifiedAt)}` : 'Avisado';
    }

    if (item.daysUntil < 0) {
        return `${Math.abs(item.daysUntil)} días de atraso`;
    }

    if (item.daysUntil === 0) {
        return 'Vence hoy';
    }

    return `Vence en ${item.daysUntil} días`;
}

async function notify(item) {
    error.value = '';
    busyId.value = item.id;

    try {
        await api(`/api/maintenance/${item.id}/notify`, { method: 'POST' });
        toast.add({
            severity: 'success',
            summary: 'Aviso registrado',
            detail: `${item.customerName} · ${item.productName}`,
            life: 3000,
        });
        emit('changed');
    } catch (exception) {
        error.value = exception.message || 'No se pudo registrar el aviso.';
    } finally {
        busyId.value = '';
    }
}

async function snooze(item, days) {
    error.value = '';
    busyId.value = item.id;

    try {
        await api(`/api/maintenance/${item.id}/snooze`, { method: 'POST', body: { days } });
        toast.add({
            severity: 'info',
            summary: 'Aviso pospuesto',
            detail: `${item.customerName}: ${days} días`,
            life: 3000,
        });
        emit('changed');
    } catch (exception) {
        error.value = exception.message || 'No se pudo posponer el aviso.';
    } finally {
        busyId.value = '';
    }
}
</script>

<template>
    <Dialog
        :visible="props.visible"
        modal
        :draggable="false"
        :header="props.title"
        :style="{ width: '720px' }"
        :breakpoints="{ '960px': '92vw', '640px': '96vw' }"
        @update:visible="emit('update:visible', $event)"
    >
        <div class="maintenance-dialog">
            <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>

            <div v-if="!props.items.length" class="dashboard-empty">
                <i class="pi pi-calendar"></i>
                <strong>Sin avisos para este día</strong>
                <span>Cuando un cliente tenga un mantenimiento programado va a aparecer acá.</span>
            </div>

            <div v-else class="maintenance-dialog-list">
                <article
                    v-for="item in props.items"
                    :key="item.id"
                    class="maintenance-item"
                    :class="{ done: item.status === 'notified' }"
                >
                    <div class="maintenance-item-head">
                        <div class="maintenance-item-copy">
                            <strong>{{ item.customerName }}</strong>
                            <small>{{ item.productName }} · {{ item.invoiceNumber }}</small>
                        </div>
                        <Tag
                            :value="maintenanceStatusLabel(item.status)"
                            :severity="maintenanceStatusSeverity(item.status)"
                        />
                    </div>

                    <div class="maintenance-item-meta">
                        <span><i class="pi pi-shopping-bag"></i> Compra: {{ formatDate(item.purchaseDate) }}</span>
                        <span><i class="pi pi-calendar"></i> Vence: {{ formatDate(item.dueDate) }}</span>
                        <span :class="{ danger: item.status === 'overdue' }">
                            <i class="pi pi-clock"></i> {{ daysText(item) }}
                        </span>
                        <span v-if="item.customerPhone"><i class="pi pi-phone"></i> {{ item.customerPhone }}</span>
                    </div>

                    <div class="maintenance-actions">
                        <Button
                            v-if="item.customerPhone"
                            as="a"
                            :href="waHref(item)"
                            target="_blank"
                            rel="noopener"
                            label="Enviar por WhatsApp"
                            icon="pi pi-whatsapp"
                            severity="success"
                            size="small"
                        />
                        <span v-else class="maintenance-no-phone">
                            <i class="pi pi-phone"></i> Sin teléfono cargado
                        </span>

                        <template v-if="item.status !== 'notified'">
                            <Button
                                label="Marcar avisado"
                                icon="pi pi-check"
                                severity="secondary"
                                outlined
                                size="small"
                                :loading="busyId === item.id"
                                @click="notify(item)"
                            />
                            <Button
                                label="Posponer 7 días"
                                size="small"
                                text
                                severity="secondary"
                                :disabled="busyId === item.id"
                                @click="snooze(item, 7)"
                            />
                            <Button
                                label="30 días"
                                size="small"
                                text
                                severity="secondary"
                                :disabled="busyId === item.id"
                                @click="snooze(item, 30)"
                            />
                        </template>
                    </div>
                </article>
            </div>
        </div>

        <template #footer>
            <Button label="Cerrar" text severity="secondary" @click="emit('update:visible', false)" />
        </template>
    </Dialog>
</template>

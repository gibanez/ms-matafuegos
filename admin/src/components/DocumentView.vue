<script setup>
import { computed, onMounted, ref } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { api } from '../api';
import { downloadDocumentPdf } from '../pdf';
import { DOCUMENTS, INVOICE_STATUS_OPTIONS, QUOTE_STATUS_OPTIONS } from '../resources';
import { pushNotification, store } from '../store';
import {
    formatDate,
    money,
    nested,
    statusLabel,
    statusSeverity,
} from '../utils';
import CustomerCreateDialog from './CustomerCreateDialog.vue';
import DocumentFormDialog from './DocumentFormDialog.vue';

const props = defineProps({
    moduleKey: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['navigate']);
const config = computed(() => DOCUMENTS[props.moduleKey]);
const isQuotes = computed(() => props.moduleKey === 'quotes');
const toast = useToast();
const confirm = useConfirm();

const rows = ref([]);
const loading = ref(false);
const search = ref('');
const customers = ref([]);
const products = ref([]);

const formVisible = ref(false);
const editingDocument = ref(null);
const customerDialogVisible = ref(false);
const pdfLoadingId = ref(null);

const statusVisible = ref(false);
const statusDocument = ref(null);
const selectedStatus = ref(null);
const statusError = ref('');
const statusSaving = ref(false);

const actionMeta = {
    edit: { icon: 'pi pi-pencil', severity: 'secondary', label: 'Editar' },
    status: { icon: 'pi pi-sync', severity: 'secondary', label: 'Cambiar estado' },
    pdf: { icon: 'pi pi-file-pdf', severity: 'secondary', label: 'Descargar PDF' },
    invoice: { icon: 'pi pi-file', severity: 'secondary', label: 'Crear factura' },
    delete: { icon: 'pi pi-trash', severity: 'danger', label: 'Eliminar' },
};

const filteredRows = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return rows.value;
    }

    return rows.value.filter((row) => config.value.search(row).includes(term));
});

function columnStyle(column) {
    return `${column.style || ''}${column.align ? `text-align:${column.align};` : ''}`;
}

function cellValue(row, field) {
    return nested(row, field);
}

function documentStatusLabel(status) {
    const options = isQuotes.value ? QUOTE_STATUS_OPTIONS : INVOICE_STATUS_OPTIONS;

    return options.find((option) => option.value === status)?.label || statusLabel(status);
}

function quoteStatusOptions(row) {
    if (!row) {
        return [];
    }

    return QUOTE_STATUS_OPTIONS.filter((option) => option.value !== row.status);
}

function invoiceStatusOptions(row) {
    if (!row) {
        return [];
    }

    const transitions = {
        draft: ['issued', 'cancelled'],
        issued: ['paid', 'cancelled'],
        paid: ['cancelled'],
        cancelled: [],
    };
    const allowed = transitions[row.status] || [];

    return allowed.map((status) => ({
        value: status,
        label: documentStatusLabel(status),
    }));
}

function statusOptionsFor(row) {
    return isQuotes.value ? quoteStatusOptions(row) : invoiceStatusOptions(row);
}

function actionsFor(row) {
    if (isQuotes.value) {
        return [
            { key: 'edit', visible: row.status !== 'cancelled' },
            { key: 'status', visible: row.status !== 'cancelled' && statusOptionsFor(row).length > 0 },
            { key: 'pdf', visible: true },
            { key: 'invoice', visible: row.status !== 'cancelled' },
            { key: 'delete', visible: true },
        ].filter((action) => action.visible);
    }

    return [
        { key: 'edit', visible: row.status === 'draft' },
        { key: 'status', visible: statusOptionsFor(row).length > 0 },
        { key: 'pdf', visible: true },
        { key: 'delete', visible: row.status === 'draft' },
    ].filter((action) => action.visible);
}

async function load() {
    loading.value = true;

    try {
        const response = await api(config.value.endpoint);
        rows.value = response[config.value.listKey] || [];
    } catch (exception) {
        toast.add({
            severity: 'error',
            summary: 'No se pudo cargar',
            detail: exception.message,
            life: 5000,
        });
    } finally {
        loading.value = false;
    }
}

async function loadLookups() {
    const results = await Promise.allSettled([
        api('/api/customers'),
        api('/api/products'),
    ]);

    if (results[0].status === 'fulfilled') {
        customers.value = results[0].value.customers || [];
    }

    if (results[1].status === 'fulfilled') {
        products.value = results[1].value.products || [];
    }
}

async function openCreate() {
    editingDocument.value = null;

    if (!customers.value.length || !products.value.length) {
        await loadLookups();
    }

    formVisible.value = true;
}

async function openEdit(row) {
    if (!customers.value.length || !products.value.length) {
        await loadLookups();
    }

    editingDocument.value = row;
    formVisible.value = true;
}

async function onSaved() {
    await load();
}

function openCustomerDialog() {
    customerDialogVisible.value = true;
}

function onCustomerCreated(customer) {
    if (!customer || !customer.id) {
        return;
    }

    if (!customers.value.some((item) => item.id === customer.id)) {
        customers.value = [...customers.value, customer];
    }
}

async function downloadPdf(row) {
    if (pdfLoadingId.value) {
        return;
    }

    pdfLoadingId.value = row.id;

    try {
        const detail = await api(`${config.value.endpoint}/${row.id}`);
        let customer = detail.customer || null;

        if (detail.customerId) {
            try {
                customer = await api(`/api/customers/${detail.customerId}`);
            } catch {
                // Si falla el detalle se usa el resumen del documento.
            }
        }

        await downloadDocumentPdf({ ...detail, customer }, props.moduleKey);

        toast.add({
            severity: 'success',
            summary: 'PDF generado',
            detail: `${detail.number || row.number}.pdf`,
            life: 3000,
        });
    } catch (exception) {
        toast.add({
            severity: 'error',
            summary: 'No se pudo generar el PDF',
            detail: exception.message,
            life: 5500,
        });
    } finally {
        pdfLoadingId.value = null;
    }
}

function openStatus(row) {
    const options = statusOptionsFor(row);

    if (!options.length) {
        toast.add({
            severity: 'info',
            summary: 'Sin cambios disponibles',
            detail: 'El documento ya está en un estado final.',
            life: 3200,
        });
        return;
    }

    statusDocument.value = row;
    selectedStatus.value = options[0].value;
    statusError.value = '';
    statusVisible.value = true;
}

async function changeStatus() {
    if (!selectedStatus.value || !statusDocument.value) {
        statusError.value = 'Seleccioná un estado.';
        return;
    }

    statusSaving.value = true;
    statusError.value = '';

    try {
        const endpoint = isQuotes.value ? '/api/quotes' : '/api/invoices';
        await api(`${endpoint}/${statusDocument.value.id}/status`, {
            method: 'PATCH',
            body: { status: selectedStatus.value },
        });

        const label = statusDocument.value.number;
        const statusText = documentStatusLabel(selectedStatus.value);

        toast.add({
            severity: 'success',
            summary: 'Estado actualizado',
            detail: `${label}: ${statusText}`,
            life: 3600,
        });
        pushNotification({
            title: 'Estado actualizado',
            detail: `${label} → ${statusText}`,
            type: 'info',
        });

        statusVisible.value = false;
        await load();
    } catch (exception) {
        statusError.value = exception.message || 'No se pudo cambiar el estado.';
        toast.add({
            severity: 'error',
            summary: 'No se pudo cambiar el estado',
            detail: exception.message,
            life: 5500,
        });
    } finally {
        statusSaving.value = false;
    }
}

function confirmDelete(row) {
    confirm.require({
        header: 'Confirmar eliminación',
        message: `¿Eliminar ${config.value.singular} "${row.number}"? Esta acción no se puede deshacer.`,
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Eliminar',
        acceptClass: 'p-button-danger',
        accept: async () => {
            try {
                await api(`${config.value.endpoint}/${row.id}`, { method: 'DELETE' });
                toast.add({
                    severity: 'success',
                    summary: 'Documento eliminado',
                    detail: row.number,
                    life: 3200,
                });
                pushNotification({
                    title: 'Documento eliminado',
                    detail: row.number,
                    type: 'error',
                });
                await load();
            } catch (exception) {
                toast.add({
                    severity: 'error',
                    summary: 'No se pudo eliminar',
                    detail: exception.message,
                    life: 5500,
                });
            }
        },
    });
}

function createInvoiceFromQuote(row) {
    confirm.require({
        header: 'Crear factura desde presupuesto',
        message: `Se va a crear una factura en borrador a partir de ${row.number} y el presupuesto quedará aceptado.`,
        icon: 'pi pi-file',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Crear factura',
        accept: async () => {
            try {
                const invoice = await api(`/api/invoices/from-quote/${row.id}`, { method: 'POST' });

                toast.add({
                    severity: 'success',
                    summary: 'Factura creada',
                    detail: `${invoice.number} generada desde ${row.number}`,
                    life: 4000,
                });
                pushNotification({
                    title: 'Factura creada desde presupuesto',
                    detail: `${invoice.number} ← ${row.number}`,
                    type: 'success',
                });

                await load();
                emit('navigate', 'invoices');
            } catch (exception) {
                toast.add({
                    severity: 'error',
                    summary: 'No se pudo crear la factura',
                    detail: exception.message,
                    life: 5500,
                });
            }
        },
    });
}

function handleAction(action, row) {
    if (action === 'edit') {
        openEdit(row);
    } else if (action === 'status') {
        openStatus(row);
    } else if (action === 'pdf') {
        downloadPdf(row);
    } else if (action === 'invoice') {
        createInvoiceFromQuote(row);
    } else if (action === 'delete') {
        confirmDelete(row);
    }
}

onMounted(async () => {
    await Promise.all([load(), loadLookups()]);

    if (store.createRequest === props.moduleKey) {
        store.createRequest = null;
        await openCreate();
    }
});
</script>

<template>
    <section class="resource-view">
        <div class="module-toolbar">
            <div class="toolbar-left">
                <IconField class="search-field">
                    <InputIcon class="pi pi-search" />
                    <InputText
                        v-model="search"
                        :placeholder="config.searchPlaceholder"
                        aria-label="Filtrar documentos"
                    />
                </IconField>
                <Button
                    v-if="search"
                    icon="pi pi-times"
                    text
                    rounded
                    severity="secondary"
                    size="small"
                    aria-label="Limpiar búsqueda"
                    @click="search = ''"
                />
                <span class="result-count">{{ filteredRows.length }} de {{ rows.length }}</span>
            </div>

            <div class="toolbar-right">
                <Button
                    label="Nuevo cliente"
                    icon="pi pi-user-plus"
                    severity="secondary"
                    outlined
                    @click="openCustomerDialog"
                />
                <Button :label="config.addLabel" icon="pi pi-plus" @click="openCreate" />
            </div>
        </div>

        <div class="table-card">
            <DataTable
                :value="filteredRows"
                data-key="id"
                :loading="loading"
                paginator
                :rows="10"
                :rows-per-page-options="[10, 20, 50]"
                :always-show-paginator="false"
                scrollable
                table-style="min-width: 900px"
                class="cms-table"
            >
                <Column
                    v-for="column in config.columns"
                    :key="column.field"
                    :field="column.field"
                    :header="column.header"
                    :sortable="column.sortable"
                    :style="columnStyle(column)"
                    :header-style="columnStyle(column)"
                >
                    <template #body="{ data }">
                        <Tag
                            v-if="column.type === 'status'"
                            :value="documentStatusLabel(cellValue(data, column.field))"
                            :severity="statusSeverity(cellValue(data, column.field))"
                        />
                        <span v-else-if="column.type === 'money'" class="money-cell">
                            {{ money(cellValue(data, column.field)) }}
                        </span>
                        <strong v-else-if="column.type === 'strong'">{{ cellValue(data, column.field) || '—' }}</strong>
                        <span v-else-if="column.type === 'date'">{{ formatDate(cellValue(data, column.field)) }}</span>
                        <span v-else>{{ cellValue(data, column.field) || '—' }}</span>
                    </template>
                </Column>

                <Column header="Acciones" style="width: 200px; text-align: right">
                    <template #body="{ data }">
                        <div class="row-actions">
                            <Button
                                v-for="action in actionsFor(data)"
                                :key="action.key"
                                :icon="actionMeta[action.key].icon"
                                :severity="actionMeta[action.key].severity"
                                text
                                rounded
                                size="small"
                                :loading="action.key === 'pdf' && pdfLoadingId === data.id"
                                :aria-label="actionMeta[action.key].label"
                                v-tooltip.top="actionMeta[action.key].label"
                                @click="handleAction(action.key, data)"
                            />
                        </div>
                    </template>
                </Column>

                <template #empty>
                    <div class="table-empty">
                        <i class="pi pi-file"></i>
                        <strong>{{ search ? 'Sin resultados' : 'Todavía no hay documentos' }}</strong>
                        <span>{{ search ? 'Probá con otro término de búsqueda.' : 'Usá el botón de arriba para crear el primero.' }}</span>
                    </div>
                </template>
            </DataTable>
        </div>

        <DocumentFormDialog
            v-model:visible="formVisible"
            :module-key="props.moduleKey"
            :document="editingDocument"
            :customers="customers"
            :products="products"
            @saved="onSaved"
            @customer-created="onCustomerCreated"
        />

        <CustomerCreateDialog
            v-model:visible="customerDialogVisible"
            @created="onCustomerCreated"
        />

        <Dialog
            v-model:visible="statusVisible"
            modal
            :draggable="false"
            header="Cambiar estado"
            :style="{ width: '440px' }"
            :breakpoints="{ '640px': '94vw' }"
        >
            <div v-if="statusDocument" class="status-dialog">
                <div class="status-document">
                    <span class="eyebrow">{{ isQuotes ? 'Presupuesto' : 'Factura' }}</span>
                    <strong>{{ statusDocument.number }}</strong>
                    <Tag
                        :value="documentStatusLabel(statusDocument.status)"
                        :severity="statusSeverity(statusDocument.status)"
                    />
                </div>

                <div class="form-field">
                    <label for="new-status">Nuevo estado</label>
                    <Select
                        id="new-status"
                        v-model="selectedStatus"
                        :options="statusOptionsFor(statusDocument)"
                        option-label="label"
                        option-value="value"
                        placeholder="Seleccionar estado"
                    />
                </div>

                <Message v-if="statusError" severity="error" :closable="false">{{ statusError }}</Message>
            </div>

            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="statusVisible = false" />
                <Button
                    label="Actualizar estado"
                    icon="pi pi-check"
                    :loading="statusSaving"
                    @click="changeStatus"
                />
            </template>
        </Dialog>
    </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { api } from '../api';
import { LOOKUPS, RESOURCES } from '../resources';
import { pushNotification, store } from '../store';
import {
    excerpt,
    formatDate,
    formatDateTime,
    money,
    nested,
    statusLabel,
    statusSeverity,
} from '../utils';
import ProductMediaDialog from './ProductMediaDialog.vue';

const props = defineProps({
    moduleKey: {
        type: String,
        required: true,
    },
});

const config = computed(() => RESOURCES[props.moduleKey]);
const toast = useToast();
const confirm = useConfirm();

const rows = ref([]);
const loading = ref(false);
const search = ref('');
const dialogVisible = ref(false);
const editing = ref(null);
const form = reactive({});
const formError = ref('');
const saving = ref(false);
const lookups = reactive({
    categories: [],
    providers: [],
});

const viewVisible = ref(false);
const selectedRow = ref(null);

const mediaVisible = ref(false);
const mediaProduct = ref(null);

const actionMeta = {
    edit: { icon: 'pi pi-pencil', severity: 'secondary', label: 'Editar' },
    delete: { icon: 'pi pi-trash', severity: 'danger', label: 'Eliminar' },
    media: { icon: 'pi pi-images', severity: 'secondary', label: 'Multimedia' },
    view: { icon: 'pi pi-eye', severity: 'secondary', label: 'Ver consulta' },
};

const filteredRows = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return rows.value;
    }

    return rows.value.filter((row) => config.value.search(row).includes(term));
});

const hasActions = computed(() => (config.value.actions || []).length > 0);

function fieldVisible(field) {
    if (!field.showWhen) {
        return true;
    }

    return Boolean(form[field.showWhen.field]) === Boolean(field.showWhen.value);
}

const visibleFields = computed(() => config.value.fields.filter(fieldVisible));

function columnStyle(column) {
    return `${column.style || ''}${column.align ? `text-align:${column.align};` : ''}`;
}

function cellValue(row, field) {
    return nested(row, field);
}

function mediaThumb(product) {
    const media = Array.isArray(product.media) ? product.media : [];
    const image = media.find((item) => item.type === 'image');

    return image?.url || null;
}

function stockClass(product) {
    if (product.lowStock) {
        return 'danger';
    }

    if (product.stock <= product.minStock * 2) {
        return 'warning';
    }

    return 'success';
}

function fieldId(field) {
    return `field-${props.moduleKey}-${field.name}`;
}

function optionsFor(field) {
    if (field.options) {
        return field.options;
    }

    if (!field.source) {
        return [];
    }

    const items = lookups[field.source] || [];
    const options = items.map((item) => ({
        label: item[LOOKUPS[field.source].label],
        value: item.id,
    }));

    if (field.emptyLabel !== undefined) {
        return [{ label: field.emptyLabel, value: null }, ...options];
    }

    return options;
}

function blankForm() {
    const values = {};

    config.value.fields.forEach((field) => {
        if (field.default !== undefined) {
            values[field.name] = field.default;
        } else if (field.type === 'toggle') {
            values[field.name] = false;
        } else if (field.type === 'number') {
            values[field.name] = null;
        } else {
            values[field.name] = '';
        }
    });

    return values;
}

function resetForm() {
    Object.keys(form).forEach((key) => delete form[key]);
    Object.assign(form, blankForm());
    formError.value = '';
}

async function loadLookups() {
    const sources = [...new Set(config.value.fields.map((field) => field.source).filter(Boolean))];

    await Promise.all(sources.map(async (source) => {
        const lookup = LOOKUPS[source];

        if (!lookup) {
            return;
        }

        try {
            const response = await api(lookup.path);
            lookups[source] = response[lookup.key] || [];
        } catch {
            lookups[source] = [];
        }
    }));
}

async function openCreate() {
    if (config.value.readOnly) {
        return;
    }

    editing.value = null;
    resetForm();
    await loadLookups();
    dialogVisible.value = true;
}

async function openEdit(row) {
    editing.value = row;
    resetForm();
    await loadLookups();

    config.value.fields.forEach((field) => {
        form[field.name] = row[field.name] ?? blankForm()[field.name];
    });

    dialogVisible.value = true;
}

function openView(row) {
    selectedRow.value = row;
    viewVisible.value = true;
}

function whatsappHref(phone) {
    let digits = String(phone || '').replace(/\D+/g, '');

    if (!digits) {
        return '#';
    }

    digits = digits.replace(/^0+/, '');

    if (digits.startsWith('549')) {
        return `https://wa.me/${digits}`;
    }

    if (digits.startsWith('54')) {
        return `https://wa.me/549${digits.slice(2).replace(/^0+/, '')}`;
    }

    return `https://wa.me/549${digits}`;
}

function openMedia(row) {
    mediaProduct.value = row;
    mediaVisible.value = true;
}

function buildPayload() {
    const payload = {};

    config.value.fields.forEach((field) => {
        let value = form[field.name];

        if (field.type === 'toggle') {
            value = Boolean(value);
        }

        if (field.nullable && (value === '' || value === undefined)) {
            value = null;
        }

        if (field.type === 'number' && value === null) {
            value = field.default ?? 0;
        }

        payload[field.name] = value;
    });

    return payload;
}

async function save() {
    formError.value = '';

    const missing = config.value.fields.filter((field) => {
        if (!field.required) {
            return false;
        }

        const value = form[field.name];

        return value === null || value === undefined || String(value).trim() === '';
    });

    if (missing.length) {
        formError.value = `Completá los campos obligatorios: ${missing.map((field) => field.label).join(', ')}.`;
        return;
    }

    const isEditing = Boolean(editing.value);
    const payload = buildPayload();

    saving.value = true;

    try {
        const result = isEditing
            ? await api(`${config.value.endpoint}/${editing.value.id}`, { method: 'PUT', body: payload })
            : await api(config.value.endpoint, { method: 'POST', body: payload });

        const label = config.value.displayName(result || payload);

        toast.add({
            severity: 'success',
            summary: isEditing ? 'Cambios guardados' : 'Registro creado',
            detail: `${config.value.title}: ${label}`,
            life: 3200,
        });
        pushNotification({
            title: isEditing ? 'Cambios guardados' : 'Registro creado',
            detail: `${config.value.title}: ${label}`,
            type: 'success',
        });

        dialogVisible.value = false;
        await load();
    } catch (exception) {
        formError.value = exception.message || 'No se pudo guardar.';
        toast.add({
            severity: 'error',
            summary: 'No se pudo guardar',
            detail: exception.message,
            life: 5000,
        });
    } finally {
        saving.value = false;
    }
}

function confirmDelete(row) {
    confirm.require({
        header: 'Confirmar eliminación',
        message: `¿Eliminar ${config.value.singular} "${config.value.displayName(row)}"? Esta acción no se puede deshacer.`,
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Eliminar',
        acceptClass: 'p-button-danger',
        accept: async () => {
            try {
                await api(`${config.value.endpoint}/${row.id}`, { method: 'DELETE' });

                toast.add({
                    severity: 'success',
                    summary: 'Registro eliminado',
                    detail: `${config.value.title}: ${config.value.displayName(row)}`,
                    life: 3200,
                });
                pushNotification({
                    title: 'Registro eliminado',
                    detail: `${config.value.title}: ${config.value.displayName(row)}`,
                    type: 'error',
                });

                await load();
            } catch (exception) {
                toast.add({
                    severity: 'error',
                    summary: 'No se pudo eliminar',
                    detail: exception.message,
                    life: 5000,
                });
            }
        },
    });
}

function handleAction(action, row) {
    if (action === 'edit') {
        openEdit(row);
    } else if (action === 'delete') {
        confirmDelete(row);
    } else if (action === 'media') {
        openMedia(row);
    } else if (action === 'view') {
        openView(row);
    }
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

onMounted(async () => {
    await load();

    if (store.createRequest === props.moduleKey && !config.value.readOnly) {
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
                        aria-label="Filtrar registros"
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
                    v-if="!config.readOnly"
                    :label="config.addLabel"
                    icon="pi pi-plus"
                    @click="openCreate"
                />
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
                        <div v-if="column.type === 'product'" class="product-cell">
                            <span class="table-thumb">
                                <img v-if="mediaThumb(data)" :src="mediaThumb(data)" :alt="data.name">
                                <i v-else class="pi pi-box"></i>
                            </span>
                            <span class="product-copy">
                                <strong>{{ data.name }}</strong>
                                <small>{{ data.slug }}</small>
                            </span>
                        </div>

                        <Tag
                            v-else-if="column.type === 'status'"
                            :value="statusLabel(cellValue(data, column.field))"
                            :severity="statusSeverity(cellValue(data, column.field))"
                        />

                        <span v-else-if="column.type === 'money'" class="money-cell">
                            {{ money(cellValue(data, column.field)) }}
                        </span>

                        <div v-else-if="column.type === 'stock'" class="stock-cell">
                            <span class="stock-pill" :class="stockClass(data)">{{ data.stock }}</span>
                            <small>mín. {{ data.minStock }}</small>
                        </div>

                        <Tag
                            v-else-if="column.type === 'maintenance'"
                            :value="data.requiresMaintenance
                                ? `Cada ${data.maintenanceDays} ${data.maintenanceDays === 1 ? 'día' : 'días'}`
                                : '—'"
                            :severity="data.requiresMaintenance ? 'info' : 'secondary'"
                        />

                        <span v-else-if="column.type === 'date'">{{ formatDate(cellValue(data, column.field)) }}</span>
                        <span v-else-if="column.type === 'datetime'">{{ formatDateTime(cellValue(data, column.field)) }}</span>
                        <span v-else-if="column.type === 'excerpt'" class="muted-cell">{{ excerpt(cellValue(data, column.field)) }}</span>
                        <code v-else-if="column.type === 'mono'">{{ cellValue(data, column.field) || '—' }}</code>
                        <strong v-else-if="column.type === 'strong'">{{ cellValue(data, column.field) || '—' }}</strong>
                        <span v-else>{{ cellValue(data, column.field) || '—' }}</span>
                    </template>
                </Column>

                <Column v-if="hasActions" header="Acciones" class="actions-column" style="width: 150px; text-align: right">
                    <template #body="{ data }">
                        <div class="row-actions">
                            <Button
                                v-for="action in config.actions"
                                :key="action"
                                :icon="actionMeta[action].icon"
                                :severity="actionMeta[action].severity"
                                text
                                rounded
                                size="small"
                                :aria-label="actionMeta[action].label"
                                v-tooltip.top="actionMeta[action].label"
                                @click="handleAction(action, data)"
                            />
                        </div>
                    </template>
                </Column>

                <template #empty>
                    <div class="table-empty">
                        <i class="pi pi-inbox"></i>
                        <strong>{{ search ? 'Sin resultados' : 'Todavía no hay registros' }}</strong>
                        <span>{{ search ? 'Probá con otro término de búsqueda.' : 'Usá el botón de arriba para crear el primero.' }}</span>
                    </div>
                </template>
            </DataTable>
        </div>

        <Dialog
            v-model:visible="dialogVisible"
            modal
            :draggable="false"
            :header="editing ? `Editar ${config.singular}` : config.addLabel"
            :style="{ width: '760px' }"
            :breakpoints="{ '960px': '92vw', '640px': '96vw' }"
        >
            <div class="resource-form">
                <div class="form-grid">
                    <div
                        v-for="field in visibleFields"
                        :key="field.name"
                        class="form-field"
                        :class="{ full: field.full }"
                    >
                        <label :for="fieldId(field)">
                            {{ field.label }}
                            <span v-if="field.required" class="required-mark">*</span>
                        </label>

                        <InputText
                            v-if="field.type === 'text'"
                            :id="fieldId(field)"
                            v-model="form[field.name]"
                            :placeholder="field.placeholder"
                        />

                        <Textarea
                            v-else-if="field.type === 'textarea'"
                            :id="fieldId(field)"
                            v-model="form[field.name]"
                            :rows="field.rows || 4"
                            auto-resize
                            :placeholder="field.placeholder"
                        />

                        <Select
                            v-else-if="field.type === 'select'"
                            :id="fieldId(field)"
                            v-model="form[field.name]"
                            :options="optionsFor(field)"
                            option-label="label"
                            option-value="value"
                            :filter="field.filter"
                            :show-clear="field.nullable"
                            :placeholder="field.placeholder || 'Seleccionar'"
                        />

                        <InputNumber
                            v-else-if="field.type === 'number'"
                            :id="fieldId(field)"
                            v-model="form[field.name]"
                            :min="field.min"
                            :max="field.max"
                            :step="field.step"
                            :min-fraction-digits="field.minFractionDigits"
                            :max-fraction-digits="field.maxFractionDigits"
                            :show-buttons="false"
                            fluid
                        />

                        <div v-else-if="field.type === 'toggle'" class="toggle-field">
                            <ToggleSwitch :id="fieldId(field)" v-model="form[field.name]" />
                            <span>{{ form[field.name] ? 'Sí' : 'No' }}</span>
                        </div>
                    </div>
                </div>

                <Message v-if="formError" severity="error" :closable="false">{{ formError }}</Message>
            </div>

            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="dialogVisible = false" />
                <Button
                    :label="editing ? 'Guardar cambios' : 'Crear'"
                    icon="pi pi-check"
                    :loading="saving"
                    @click="save"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="viewVisible"
            modal
            :draggable="false"
            header="Detalle de la consulta"
            :style="{ width: '620px' }"
            :breakpoints="{ '640px': '96vw' }"
        >
            <div v-if="selectedRow" class="inquiry-view">
                <div class="inquiry-meta">
                    <div>
                        <small>Nombre</small>
                        <strong>{{ selectedRow.name }}</strong>
                    </div>
                    <div>
                        <small>Email</small>
                        <strong>{{ selectedRow.email }}</strong>
                    </div>
                    <div v-if="selectedRow.phone">
                        <small>WhatsApp</small>
                        <strong>{{ selectedRow.phone }}</strong>
                    </div>
                    <div>
                        <small>Fecha</small>
                        <strong>{{ formatDateTime(selectedRow.createdAt) }}</strong>
                    </div>
                </div>
                <div class="inquiry-message">{{ selectedRow.message }}</div>
            </div>

            <template #footer>
                <Button label="Cerrar" text severity="secondary" @click="viewVisible = false" />
                <Button
                    v-if="selectedRow && selectedRow.phone"
                    as="a"
                    :href="whatsappHref(selectedRow.phone)"
                    target="_blank"
                    rel="noopener"
                    label="WhatsApp"
                    icon="pi pi-comments"
                    severity="success"
                />
                <Button
                    v-if="selectedRow"
                    as="a"
                    :href="`mailto:${selectedRow.email}`"
                    label="Responder"
                    icon="pi pi-envelope"
                />
            </template>
        </Dialog>

        <ProductMediaDialog
            v-if="props.moduleKey === 'products'"
            v-model:visible="mediaVisible"
            :product="mediaProduct"
            @changed="load"
        />
    </section>
</template>

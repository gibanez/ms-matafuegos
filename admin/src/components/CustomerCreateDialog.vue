<script setup>
import { reactive, ref, watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import { api } from '../api';
import { RESOURCES } from '../resources';
import { pushNotification } from '../store';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    customer: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:visible', 'created']);

const toast = useToast();
const fields = RESOURCES.customers.fields;
const form = reactive({});
const formError = ref('');
const saving = ref(false);

function blankForm() {
    const values = {};

    fields.forEach((field) => {
        if (field.default !== undefined) {
            values[field.name] = field.default;
        } else {
            values[field.name] = '';
        }
    });

    return values;
}

function resetForm() {
    Object.keys(form).forEach((key) => delete form[key]);
    Object.assign(form, blankForm());

    fields.forEach((field) => {
        if (props.customer && props.customer[field.name] !== undefined && props.customer[field.name] !== null) {
            form[field.name] = props.customer[field.name];
        }
    });

    formError.value = '';
}

function fieldId(field) {
    return `quick-customer-${field.name}`;
}

function buildPayload() {
    const payload = {};

    fields.forEach((field) => {
        let value = form[field.name];

        if (field.nullable && (value === '' || value === undefined)) {
            value = null;
        }

        if (typeof value === 'string') {
            value = value.trim();

            if (field.nullable && value === '') {
                value = null;
            }
        }

        payload[field.name] = value;
    });

    return payload;
}

async function save() {
    formError.value = '';

    const missing = fields.filter((field) => {
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

    saving.value = true;

    try {
        const customer = props.customer
            ? await api(`/api/customers/${props.customer.id}`, { method: 'PUT', body: buildPayload() })
            : await api('/api/customers', { method: 'POST', body: buildPayload() });

        toast.add({
            severity: 'success',
            summary: props.customer ? 'Cliente actualizado' : 'Cliente creado',
            detail: customer?.name || form.name,
            life: 3200,
        });
        pushNotification({
            title: props.customer ? 'Cliente actualizado' : 'Cliente creado',
            detail: customer?.name || form.name,
            type: 'success',
        });

        emit('created', customer);
        emit('update:visible', false);
    } catch (exception) {
        formError.value = exception.message || 'No se pudo guardar el cliente.';
        toast.add({
            severity: 'error',
            summary: 'No se pudo guardar',
            detail: exception.message,
            life: 5500,
        });
    } finally {
        saving.value = false;
    }
}

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            resetForm();
        }
    },
    { immediate: true },
);
</script>

<template>
    <Dialog
        :visible="props.visible"
        modal
        :draggable="false"
        :header="props.customer ? 'Editar cliente' : 'Nuevo cliente'"
        :style="{ width: '640px' }"
        :breakpoints="{ '720px': '95vw' }"
        @update:visible="emit('update:visible', $event)"
    >
        <div class="resource-form">
            <div class="form-grid">
                <div
                    v-for="field in fields"
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
                        :rows="field.rows || 3"
                        auto-resize
                        :placeholder="field.placeholder"
                    />

                    <Select
                        v-else-if="field.type === 'select'"
                        :id="fieldId(field)"
                        v-model="form[field.name]"
                        :options="field.options || []"
                        option-label="label"
                        option-value="value"
                        placeholder="Seleccionar"
                    />
                </div>
            </div>

            <Message v-if="formError" severity="error" :closable="false">{{ formError }}</Message>
        </div>

        <template #footer>
            <Button label="Cancelar" text severity="secondary" @click="emit('update:visible', false)" />
            <Button
                :label="props.customer ? 'Guardar cambios' : 'Crear cliente'"
                icon="pi pi-check"
                :loading="saving"
                @click="save"
            />
        </template>
    </Dialog>
</template>

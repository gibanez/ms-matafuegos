<script setup>
import { computed, reactive, ref, watch } from 'vue';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import { api } from '../api';
import {
    INVOICE_STATUS_OPTIONS,
    PAYMENT_METHOD_OPTIONS,
    QUOTE_STATUS_OPTIONS,
} from '../resources';
import { pushNotification } from '../store';
import {
    dateToYmd,
    money,
    roundMoney,
    todayYmd,
    ymdToDate,
} from '../utils';
import CustomerCreateDialog from './CustomerCreateDialog.vue';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    moduleKey: {
        type: String,
        required: true,
    },
    document: {
        type: Object,
        default: null,
    },
    customers: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:visible', 'saved', 'customer-created']);
const toast = useToast();

const saving = ref(false);
const formError = ref('');
const customerDialogVisible = ref(false);

const form = reactive({
    customerId: null,
    issueDate: null,
    validUntil: null,
    dueDate: null,
    status: 'draft',
    paymentMethod: 'cash',
    notes: '',
    lines: [],
    adjustments: [],
});

const isQuote = computed(() => props.moduleKey === 'quotes');
const isEditing = computed(() => Boolean(props.document));
const title = computed(() => {
    const label = isQuote.value ? 'presupuesto' : 'factura';

    if (props.document?.number) {
        return `Editar ${label} ${props.document.number}`;
    }

    return isQuote.value ? 'Nuevo presupuesto' : 'Nueva factura';
});

const customerOptions = computed(() => props.customers.map((customer) => ({
    label: customer.taxId ? `${customer.name} · ${customer.taxId}` : customer.name,
    value: customer.id,
})));

const productOptions = computed(() => props.products.map((product) => ({
    label: product.sku ? `${product.name} · ${product.sku}` : product.name,
    value: product.id,
})));

const statusOptions = computed(() => (isQuote.value ? QUOTE_STATUS_OPTIONS : INVOICE_STATUS_OPTIONS));

const lineSubtotal = computed(() => form.lines.reduce((total, line) => total + lineTotal(line), 0));
const adjustmentsTotal = computed(() => form.adjustments.reduce((total, adjustment) => total + adjustmentAmount(adjustment), 0));
const total = computed(() => roundMoney(lineSubtotal.value + adjustmentsTotal.value));

function blankLine() {
    return {
        productId: null,
        description: '',
        quantity: 1,
        unitPrice: null,
    };
}

function blankAdjustment() {
    return {
        description: '',
        kind: 'surcharge',
        valueType: 'fixed',
        value: null,
    };
}

function lineTotal(line) {
    return roundMoney((Number(line.quantity) || 0) * (Number(line.unitPrice) || 0));
}

function adjustmentBase() {
    return lineSubtotal.value;
}

function adjustmentAmount(adjustment) {
    const value = Number(adjustment.value) || 0;
    const amount = adjustment.valueType === 'percentage'
        ? adjustmentBase() * value / 100
        : value;

    return adjustment.kind === 'discount' ? -roundMoney(amount) : roundMoney(amount);
}

function hydrate(document) {
    form.customerId = document?.customerId || null;
    form.issueDate = ymdToDate(document?.issueDate) || ymdToDate(todayYmd());
    form.validUntil = ymdToDate(document?.validUntil);
    form.dueDate = ymdToDate(document?.dueDate);
    form.status = document?.status || 'draft';
    form.paymentMethod = document?.paymentMethod || 'cash';
    form.notes = document?.notes || '';
    form.lines = Array.isArray(document?.lines) && document.lines.length
        ? document.lines.map((line) => ({
            productId: line.productId || null,
            description: line.description || '',
            quantity: Number(line.quantity) || 1,
            unitPrice: line.unitPrice === null || line.unitPrice === undefined ? null : Number(line.unitPrice),
        }))
        : [blankLine()];
    form.adjustments = Array.isArray(document?.adjustments)
        ? document.adjustments.map((adjustment) => ({
            description: adjustment.description || '',
            kind: adjustment.kind || 'surcharge',
            valueType: adjustment.valueType || 'fixed',
            value: adjustment.value === null || adjustment.value === undefined ? null : Number(adjustment.value),
        }))
        : [];
    formError.value = '';
}

function addLine() {
    form.lines.push(blankLine());
}

function removeLine(index) {
    if (form.lines.length === 1) {
        form.lines[index] = blankLine();
        return;
    }

    form.lines.splice(index, 1);
}

function addAdjustment() {
    form.adjustments.push(blankAdjustment());
}

function removeAdjustment(index) {
    form.adjustments.splice(index, 1);
}

function onProductChange(line) {
    if (!line.productId) {
        line.description = '';
        line.unitPrice = null;
        return;
    }

    const product = props.products.find((item) => item.id === line.productId);

    if (!product) {
        return;
    }

    line.description = product.name || '';
    line.unitPrice = product.price === null || product.price === undefined ? null : Number(product.price);
}

function openCustomerDialog() {
    customerDialogVisible.value = true;
}

function onCustomerCreated(customer) {
    if (!customer || !customer.id) {
        return;
    }

    form.customerId = customer.id;
    formError.value = '';
    emit('customer-created', customer);
}

function validate() {
    if (!form.customerId) {
        return 'Seleccioná un cliente.';
    }

    if (!form.lines.length) {
        return 'Agregá al menos una línea al documento.';
    }

    for (let index = 0; index < form.lines.length; index += 1) {
        const line = form.lines[index];
        const position = index + 1;
        const quantity = Number(line.quantity);

        if (!Number.isFinite(quantity) || quantity <= 0) {
            return `La cantidad de la línea ${position} debe ser mayor a cero.`;
        }

        const price = line.unitPrice === null || line.unitPrice === undefined || line.unitPrice === ''
            ? null
            : Number(line.unitPrice);

        if (price !== null && (!Number.isFinite(price) || price < 0)) {
            return `El precio de la línea ${position} no es válido.`;
        }

        if (!line.productId && !String(line.description || '').trim()) {
            return `La línea libre ${position} necesita una descripción.`;
        }

        if (!line.productId && price === null) {
            return `La línea libre ${position} necesita un precio unitario.`;
        }
    }

    for (let index = 0; index < form.adjustments.length; index += 1) {
        const adjustment = form.adjustments[index];
        const position = index + 1;
        const value = adjustment.value === null || adjustment.value === undefined || adjustment.value === ''
            ? null
            : Number(adjustment.value);

        if (!String(adjustment.description || '').trim()) {
            return `El ajuste ${position} necesita una descripción.`;
        }

        if (value === null || !Number.isFinite(value) || value < 0) {
            return `El valor del ajuste ${position} no es válido.`;
        }

        if (adjustment.valueType === 'percentage' && value > 100) {
            return `El porcentaje del ajuste ${position} no puede superar 100.`;
        }
    }

    if (total.value < 0) {
        return 'El total del documento no puede ser negativo.';
    }

    return '';
}

function buildPayload() {
    const payload = {
        customerId: form.customerId,
        issueDate: dateToYmd(form.issueDate),
        notes: String(form.notes || '').trim() || null,
        lines: form.lines.map((line) => ({
            productId: line.productId || null,
            description: String(line.description || '').trim() || null,
            quantity: Number(line.quantity),
            unitPrice: line.unitPrice === null || line.unitPrice === undefined || line.unitPrice === ''
                ? null
                : Number(line.unitPrice),
        })),
        adjustments: form.adjustments.map((adjustment) => ({
            description: String(adjustment.description || '').trim(),
            kind: adjustment.kind,
            valueType: adjustment.valueType,
            value: Number(adjustment.value),
        })),
    };

    if (isQuote.value) {
        payload.validUntil = dateToYmd(form.validUntil);
        payload.status = form.status;
    } else {
        payload.dueDate = dateToYmd(form.dueDate);
        payload.paymentMethod = form.paymentMethod || null;
    }

    return payload;
}

async function save() {
    formError.value = validate();

    if (formError.value) {
        return;
    }

    saving.value = true;

    try {
        const payload = buildPayload();
        const endpoint = isQuote.value ? '/api/quotes' : '/api/invoices';
        const result = props.document
            ? await api(`${endpoint}/${props.document.id}`, { method: 'PUT', body: payload })
            : await api(endpoint, { method: 'POST', body: payload });

        if (props.document && isQuote.value && props.document.status !== form.status) {
            await api(`/api/quotes/${props.document.id}/status`, {
                method: 'PATCH',
                body: { status: form.status },
            });
        }

        const label = result?.number || props.document?.number || '';
        const documentLabel = isQuote.value ? 'Presupuesto' : 'Factura';

        toast.add({
            severity: 'success',
            summary: props.document ? 'Documento actualizado' : 'Documento creado',
            detail: label ? `${documentLabel} ${label}` : `${documentLabel} guardado`,
            life: 3600,
        });
        pushNotification({
            title: props.document ? 'Documento actualizado' : 'Documento creado',
            detail: label ? `${documentLabel}: ${label}` : documentLabel,
            type: 'success',
        });

        emit('saved', result);
        emit('update:visible', false);
    } catch (exception) {
        formError.value = exception.message || 'No se pudo guardar el documento.';
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
            hydrate(props.document);
        }
    },
    { immediate: true },
);

watch(
    () => props.document,
    (document) => {
        if (props.visible) {
            hydrate(document);
        }
    },
);
</script>

<template>
    <Dialog
        :visible="props.visible"
        modal
        maximizable
        :draggable="false"
        :header="title"
        :style="{ width: '1080px' }"
        :breakpoints="{ '1200px': '95vw', '768px': '98vw' }"
        @update:visible="emit('update:visible', $event)"
    >
        <div class="document-form">
            <Message v-if="!customers.length" severity="warn" :closable="false">
                <div class="message-with-action">
                    <span>Todavía no hay clientes cargados. Creá uno para poder guardar el documento.</span>
                    <Button
                        label="Crear cliente"
                        icon="pi pi-user-plus"
                        size="small"
                        text
                        @click="openCustomerDialog"
                    />
                </div>
            </Message>

            <Message v-if="formError" severity="error" :closable="false">{{ formError }}</Message>

            <div class="form-grid doc-meta">
                <div class="form-field full">
                    <label for="doc-customer">Cliente <span class="required-mark">*</span></label>
                    <div class="field-inline">
                        <Select
                            id="doc-customer"
                            v-model="form.customerId"
                            :options="customerOptions"
                            option-label="label"
                            option-value="value"
                            filter
                            placeholder="Seleccionar cliente"
                            :empty-message="customers.length ? 'Sin coincidencias' : 'No hay clientes cargados'"
                        />
                        <Button
                            label="Nuevo cliente"
                            icon="pi pi-user-plus"
                            severity="secondary"
                            outlined
                            @click="openCustomerDialog"
                        />
                    </div>
                </div>

                <div class="form-field">
                    <label for="doc-issue">Fecha de emisión</label>
                    <DatePicker
                        id="doc-issue"
                        v-model="form.issueDate"
                        date-format="dd/mm/yy"
                        show-icon
                        fluid
                    />
                </div>

                <div v-if="isQuote" class="form-field">
                    <label for="doc-valid">Válido hasta</label>
                    <DatePicker
                        id="doc-valid"
                        v-model="form.validUntil"
                        date-format="dd/mm/yy"
                        show-icon
                        fluid
                    />
                </div>

                <div v-else class="form-field">
                    <label for="doc-due">Vencimiento</label>
                    <DatePicker
                        id="doc-due"
                        v-model="form.dueDate"
                        date-format="dd/mm/yy"
                        show-icon
                        fluid
                    />
                </div>

                <div v-if="isQuote" class="form-field">
                    <label for="doc-status">Estado</label>
                    <Select
                        id="doc-status"
                        v-model="form.status"
                        :options="statusOptions"
                        option-label="label"
                        option-value="value"
                    />
                </div>

                <div v-else class="form-field">
                    <label for="doc-payment">Forma de pago</label>
                    <Select
                        id="doc-payment"
                        v-model="form.paymentMethod"
                        :options="PAYMENT_METHOD_OPTIONS"
                        option-label="label"
                        option-value="value"
                    />
                </div>

                <div class="form-field full">
                    <label for="doc-notes">Notas</label>
                    <Textarea id="doc-notes" v-model="form.notes" rows="2" auto-resize placeholder="Observaciones del documento" />
                </div>
            </div>

            <section class="doc-section">
                <div class="doc-section-head">
                    <div>
                        <h3>Detalle</h3>
                        <p>Elegí un producto o cargá una línea libre con descripción y precio.</p>
                    </div>
                    <Button label="Agregar línea" icon="pi pi-plus" text size="small" @click="addLine" />
                </div>

                <div class="doc-line-head">
                    <span>Producto</span>
                    <span>Descripción</span>
                    <span>Cant.</span>
                    <span>Precio unit.</span>
                    <span class="align-right">Total</span>
                    <span></span>
                </div>

                <div v-for="(line, index) in form.lines" :key="`line-${index}`" class="doc-line">
                    <Select
                        v-model="line.productId"
                        :options="productOptions"
                        option-label="label"
                        option-value="value"
                        filter
                        show-clear
                        placeholder="Línea libre"
                        :empty-message="products.length ? 'Sin coincidencias' : 'No hay productos'"
                        @change="onProductChange(line)"
                    />
                    <InputText v-model="line.description" placeholder="Descripción" />
                    <InputNumber
                        v-model="line.quantity"
                        :min="1"
                        :max-fraction-digits="0"
                        :show-buttons="false"
                        fluid
                    />
                    <InputNumber
                        v-model="line.unitPrice"
                        :min="0"
                        :min-fraction-digits="2"
                        :max-fraction-digits="2"
                        :show-buttons="false"
                        fluid
                    />
                    <span class="doc-line-total">{{ money(lineTotal(line)) }}</span>
                    <Button
                        icon="pi pi-trash"
                        text
                        rounded
                        size="small"
                        severity="danger"
                        aria-label="Quitar línea"
                        v-tooltip.top="'Quitar línea'"
                        @click="removeLine(index)"
                    />
                </div>
            </section>

            <section class="doc-section">
                <div class="doc-section-head">
                    <div>
                        <h3>Ajustes</h3>
                        <p>Sumá recargos o descuentos fijos o porcentuales sobre el subtotal.</p>
                    </div>
                    <Button label="Agregar ajuste" icon="pi pi-plus" text size="small" @click="addAdjustment" />
                </div>

                <div v-if="form.adjustments.length" class="doc-adjustment-head">
                    <span>Descripción</span>
                    <span>Tipo</span>
                    <span>Valor</span>
                    <span>Importe</span>
                    <span></span>
                </div>

                <div v-for="(adjustment, index) in form.adjustments" :key="`adjustment-${index}`" class="doc-adjustment">
                    <InputText v-model="adjustment.description" placeholder="Ej. Envío o descuento" />
                    <Select
                        v-model="adjustment.kind"
                        :options="[{ label: 'Recargo', value: 'surcharge' }, { label: 'Descuento', value: 'discount' }]"
                        option-label="label"
                        option-value="value"
                    />
                    <div class="adjustment-value">
                        <Select
                            v-model="adjustment.valueType"
                            :options="[{ label: 'Monto', value: 'fixed' }, { label: 'Porcentaje', value: 'percentage' }]"
                            option-label="label"
                            option-value="value"
                        />
                        <InputNumber
                            v-model="adjustment.value"
                            :min="0"
                            :min-fraction-digits="adjustment.valueType === 'fixed' ? 2 : 0"
                            :max-fraction-digits="2"
                            :suffix="adjustment.valueType === 'percentage' ? ' %' : ''"
                            :show-buttons="false"
                            fluid
                        />
                    </div>
                    <span class="doc-line-total" :class="{ negative: adjustmentAmount(adjustment) < 0 }">
                        {{ money(adjustmentAmount(adjustment)) }}
                    </span>
                    <Button
                        icon="pi pi-trash"
                        text
                        rounded
                        size="small"
                        severity="danger"
                        aria-label="Quitar ajuste"
                        v-tooltip.top="'Quitar ajuste'"
                        @click="removeAdjustment(index)"
                    />
                </div>

                <div v-if="!form.adjustments.length" class="doc-adjustment-empty">
                    <i class="pi pi-sliders-h"></i>
                    <span>Sin ajustes. El total es el subtotal de las líneas.</span>
                </div>
            </section>

            <div class="doc-totals">
                <div class="doc-total-row">
                    <span>Subtotal</span>
                    <strong>{{ money(lineSubtotal) }}</strong>
                </div>
                <div class="doc-total-row">
                    <span>Ajustes</span>
                    <strong :class="{ negative: adjustmentsTotal < 0 }">{{ money(adjustmentsTotal) }}</strong>
                </div>
                <div class="doc-total-row grand-total">
                    <span>Total</span>
                    <strong>{{ money(total) }}</strong>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" text severity="secondary" @click="emit('update:visible', false)" />
            <Button
                :label="isEditing ? 'Guardar cambios' : 'Crear documento'"
                icon="pi pi-check"
                :loading="saving"
                @click="save"
            />
        </template>
    </Dialog>

    <CustomerCreateDialog
        v-model:visible="customerDialogVisible"
        @created="onCustomerCreated"
    />
</template>

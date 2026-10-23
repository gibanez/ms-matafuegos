<script setup>
import { computed, onMounted, ref } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Message from 'primevue/message';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { api } from '../api';
import { pushNotification } from '../store';
import { formatDateTime } from '../utils';

const confirm = useConfirm();
const toast = useToast();

const loading = ref(true);
const running = ref(false);
const error = ref('');
const runError = ref('');
const status = ref(null);
const result = ref(null);

const pending = computed(() => status.value?.pending || 0);
const upToDate = computed(() => status.value !== null && pending.value === 0);

async function load() {
    loading.value = true;
    error.value = '';

    try {
        status.value = await api('/api/migrations/status');
    } catch (requestError) {
        error.value = requestError.data?.detail || requestError.message;
    } finally {
        loading.value = false;
    }
}

function askRun() {
    if (!status.value || pending.value === 0 || running.value) {
        return;
    }

    confirm.require({
        message: `Se van a ejecutar ${pending.value} migración${pending.value === 1 ? '' : 'es'} pendiente${pending.value === 1 ? '' : 's'}. No se puede deshacer automáticamente. ¿Continuar?`,
        header: 'Ejecutar migraciones',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ejecutar',
        rejectLabel: 'Cancelar',
        acceptClass: 'p-button-danger',
        accept: run,
    });
}

async function run() {
    running.value = true;
    runError.value = '';
    result.value = null;

    try {
        const data = await api('/api/migrations/run', { method: 'POST' });
        result.value = data;
        status.value = data.status;

        toast.add({
            severity: 'success',
            summary: 'Migraciones ejecutadas',
            detail: data.count
                ? `${data.count} migración${data.count === 1 ? '' : 'es'} aplicada${data.count === 1 ? '' : 's'}.`
                : 'La base ya estaba al día.',
            life: 3500,
        });

        if (data.count) {
            pushNotification({
                title: 'Base de datos actualizada',
                detail: `${data.count} migración${data.count === 1 ? '' : 'es'} aplicada${data.count === 1 ? '' : 's'}.`,
            });
        }
    } catch (requestError) {
        runError.value = requestError.data?.detail || requestError.message;
        result.value = {
            applied: [],
            count: 0,
            output: requestError.data?.output || '',
            failed: true,
        };

        toast.add({
            severity: 'error',
            summary: 'No se pudieron ejecutar las migraciones',
            detail: runError.value,
            life: 6000,
        });
    } finally {
        running.value = false;
    }
}

onMounted(load);
</script>

<template>
    <section class="migration-view">
        <div class="migration-hero">
            <div>
                <span class="eyebrow">Sistema</span>
                <h2>Migraciones de la base</h2>
                <p>
                    Actualizá la estructura de SQLite desde el panel, sin acceso a una terminal.
                    Solo se ejecutan las migraciones pendientes; no se revierte nada.
                </p>
            </div>

            <div class="migration-hero-actions">
                <Button
                    label="Actualizar estado"
                    icon="pi pi-refresh"
                    severity="secondary"
                    outlined
                    :loading="loading"
                    :disabled="running"
                    @click="load"
                />
                <Button
                    :label="running ? 'Ejecutando...' : 'Ejecutar migraciones'"
                    icon="pi pi-database"
                    :loading="running"
                    :disabled="loading || running || pending === 0"
                    @click="askRun"
                />
            </div>
        </div>

        <Message v-if="error" severity="error" :closable="false">
            {{ error }}
        </Message>

        <Message v-if="runError" severity="error" :closable="false">
            {{ runError }}
        </Message>

        <div class="migration-summary">
            <div class="migration-summary-card">
                <span class="migration-icon tone-blue"><i class="pi pi-database"></i></span>
                <div>
                    <small>Base de datos</small>
                    <strong>SQLite</strong>
                    <span>{{ status?.database || 'database.sqlite' }}</span>
                </div>
            </div>

            <div class="migration-summary-card">
                <span class="migration-icon tone-green"><i class="pi pi-check-circle"></i></span>
                <div>
                    <small>Aplicadas</small>
                    <strong>{{ status?.applied ?? '—' }} <em>de {{ status?.total ?? '—' }}</em></strong>
                    <span>Migraciones registradas</span>
                </div>
            </div>

            <div class="migration-summary-card">
                <span class="migration-icon" :class="pending ? 'tone-amber' : 'tone-green'">
                    <i :class="pending ? 'pi pi-exclamation-triangle' : 'pi pi-check'"></i>
                </span>
                <div>
                    <small>Pendientes</small>
                    <strong>{{ status?.pending ?? '—' }}</strong>
                    <span>{{ upToDate ? 'La base está al día' : 'Requieren ejecutarse' }}</span>
                </div>
            </div>
        </div>

        <div class="table-card migration-table-card">
            <DataTable
                :value="status?.migrations || []"
                data-key="version"
                :loading="loading"
                row-hover
                scrollable
                class="cms-table"
            >
                <Column field="version" header="Versión" style="width: 180px">
                    <template #body="{ data }">
                        <code class="migration-version">{{ data.version }}</code>
                    </template>
                </Column>
                <Column field="name" header="Migración" />
                <Column field="status" header="Estado" style="width: 130px">
                    <template #body="{ data }">
                        <Tag
                            :value="data.status === 'applied' ? 'Aplicada' : 'Pendiente'"
                            :severity="data.status === 'applied' ? 'success' : 'warn'"
                        />
                    </template>
                </Column>
                <Column field="appliedAt" header="Aplicada el" style="width: 190px">
                    <template #body="{ data }">
                        <span class="migration-date">{{ data.appliedAt ? formatDateTime(data.appliedAt) : '—' }}</span>
                    </template>
                </Column>
            </DataTable>
        </div>

        <div v-if="result" class="migration-result" :class="{ failed: result.failed }">
            <div class="migration-result-head">
                <span class="migration-icon" :class="result.failed ? 'tone-red' : 'tone-green'">
                    <i :class="result.failed ? 'pi pi-times-circle' : 'pi pi-check-circle'"></i>
                </span>
                <div>
                    <strong>
                        {{ result.failed
                            ? 'La migración no terminó'
                            : result.count
                                ? `${result.count} migración${result.count === 1 ? '' : 'es'} aplicada${result.count === 1 ? '' : 's'}`
                                : 'No había migraciones pendientes' }}
                    </strong>
                    <p v-if="result.failed">Revisá el detalle y volvé a intentar cuando esté corregido.</p>
                    <p v-else>El estado de la base se actualizó automáticamente.</p>
                </div>
            </div>

            <ul v-if="result.applied?.length" class="migration-applied-list">
                <li v-for="migration in result.applied" :key="migration.version">
                    <i class="pi pi-check"></i>
                    <span>{{ migration.name }}</span>
                    <code>{{ migration.version }}</code>
                </li>
            </ul>

            <pre v-if="result.output" class="migration-output">{{ result.output }}</pre>
        </div>
    </section>
</template>

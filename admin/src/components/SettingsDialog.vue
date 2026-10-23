<script setup>
import { reactive, watch } from 'vue';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import ToggleSwitch from 'primevue/toggleswitch';
import { useToast } from 'primevue/usetoast';
import {
    clearNotifications,
    resetPrefs,
    store,
    updatePrefs,
} from '../store';
import { initials } from '../utils';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:visible']);
const toast = useToast();

const draft = reactive({
    compact: false,
    saveToasts: true,
});

const adminName = store.admin?.name || store.admin?.email || 'Administrador';
const adminEmail = store.admin?.email || 'admin@msmatafuegos.com';

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            draft.compact = Boolean(store.prefs.compact);
            draft.saveToasts = Boolean(store.prefs.saveToasts);
        }
    },
    { immediate: true },
);

function save() {
    updatePrefs({
        compact: draft.compact,
        saveToasts: draft.saveToasts,
    });

    toast.add({
        severity: 'success',
        summary: 'Preferencias guardadas',
        detail: 'Los cambios se aplicaron al panel.',
        life: 2800,
    });
    emit('update:visible', false);
}

function reset() {
    resetPrefs();
    draft.compact = false;
    draft.saveToasts = true;
    toast.add({
        severity: 'info',
        summary: 'Preferencias restablecidas',
        detail: 'El panel volvió a la configuración inicial.',
        life: 2800,
    });
}

function clearAllNotifications() {
    clearNotifications();
    toast.add({
        severity: 'info',
        summary: 'Notificaciones',
        detail: 'Se borraron todas las notificaciones locales.',
        life: 2800,
    });
}
</script>

<template>
    <Dialog
        :visible="props.visible"
        modal
        :draggable="false"
        header="Configuración"
        :style="{ width: '560px' }"
        :breakpoints="{ '640px': '94vw' }"
        @update:visible="emit('update:visible', $event)"
    >
        <div class="settings-dialog">
            <section class="settings-section">
                <div>
                    <h3>Apariencia</h3>
                    <p>El panel usa una paleta oscura con negro, gris y azul.</p>
                </div>

                <div class="theme-swatches" aria-label="Paleta de colores">
                    <span class="swatch black" title="Negro"></span>
                    <span class="swatch gray" title="Gris"></span>
                    <span class="swatch blue" title="Azul"></span>
                </div>
            </section>

            <section class="settings-section">
                <div class="settings-row">
                    <div>
                        <strong>Tablas compactas</strong>
                        <p>Reduce el espacio entre filas para ver más registros.</p>
                    </div>
                    <ToggleSwitch v-model="draft.compact" />
                </div>

                <div class="settings-row">
                    <div>
                        <strong>Notificaciones al guardar</strong>
                        <p>Muestra un aviso cada vez que una acción termina bien.</p>
                    </div>
                    <ToggleSwitch v-model="draft.saveToasts" />
                </div>
            </section>

            <section class="settings-section">
                <h3>Cuenta</h3>
                <div class="settings-account">
                    <Avatar :label="initials(adminName, adminEmail)" shape="circle" size="large" />
                    <div>
                        <strong>{{ adminName }}</strong>
                        <span>{{ adminEmail }}</span>
                    </div>
                    <span class="account-role">Administrador</span>
                </div>
            </section>

            <section class="settings-section">
                <div class="settings-row">
                    <div>
                        <strong>Notificaciones locales</strong>
                        <p>Se guardan en este navegador. No se comparten entre dispositivos.</p>
                    </div>
                    <Button
                        label="Borrar"
                        icon="pi pi-trash"
                        text
                        severity="danger"
                        size="small"
                        @click="clearAllNotifications"
                    />
                </div>
            </section>
        </div>

        <template #footer>
            <Button label="Restablecer" text severity="secondary" @click="reset" />
            <Button label="Guardar preferencias" icon="pi pi-check" @click="save" />
        </template>
    </Dialog>
</template>

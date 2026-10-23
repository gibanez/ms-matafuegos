<script setup>
import { computed, ref } from 'vue';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import OverlayBadge from 'primevue/overlaybadge';
import Popover from 'primevue/popover';
import { useToast } from 'primevue/usetoast';
import { clearNotifications, markNotificationsRead, store } from '../store';
import { initials, timeAgo } from '../utils';

const props = defineProps({
    meta: {
        type: Object,
        required: true,
    },
    unread: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['menu', 'settings', 'logout']);
const toast = useToast();

const profileMenu = ref(null);
const notificationsPanel = ref(null);

const adminName = computed(() => store.admin?.name || store.admin?.email || 'Administrador');
const adminEmail = computed(() => store.admin?.email || 'admin@msmatafuegos.com');
const avatarLabel = computed(() => initials(adminName.value, adminEmail.value));

const profileItems = computed(() => [
    {
        label: 'Configuración',
        icon: 'pi pi-cog',
        command: () => emit('settings'),
    },
    {
        label: 'Ver sitio',
        icon: 'pi pi-external-link',
        command: () => window.open('/', '_blank', 'noopener'),
    },
    { separator: true },
    {
        label: 'Cerrar sesión',
        icon: 'pi pi-sign-out',
        command: () => emit('logout'),
    },
]);

const recentNotifications = computed(() => store.notifications.slice(0, 8));

function toggleProfile(event) {
    profileMenu.value.toggle(event);
}

function toggleNotifications(event) {
    notificationsPanel.value.toggle(event);
}

function notificationIcon(type) {
    return {
        success: 'pi pi-check-circle',
        error: 'pi pi-exclamation-circle',
        info: 'pi pi-info-circle',
        warning: 'pi pi-exclamation-triangle',
    }[type] || 'pi pi-bell';
}

function markRead() {
    markNotificationsRead();
    toast.add({ severity: 'info', summary: 'Notificaciones', detail: 'Todas marcadas como leídas.', life: 2200 });
}

function clearAll() {
    clearNotifications();
    toast.add({ severity: 'info', summary: 'Notificaciones', detail: 'Bandeja vaciada.', life: 2200 });
}
</script>

<template>
    <header class="app-topbar">
        <div class="topbar-left">
            <Button
                icon="pi pi-bars"
                text
                rounded
                severity="secondary"
                class="mobile-only"
                aria-label="Abrir menú"
                @click="emit('menu')"
            />

            <div class="page-heading">
                <span class="eyebrow">{{ meta.eyebrow }}</span>
                <h1>{{ meta.title }}</h1>
            </div>
        </div>

        <div class="topbar-right">
            <Button
                as="a"
                href="/"
                target="_blank"
                rel="noopener"
                icon="pi pi-external-link"
                text
                rounded
                severity="secondary"
                aria-label="Ver sitio"
                v-tooltip.bottom="'Ver sitio'"
            />

            <Button
                icon="pi pi-cog"
                text
                rounded
                severity="secondary"
                aria-label="Configuración"
                v-tooltip.bottom="'Configuración'"
                @click="emit('settings')"
            />

            <OverlayBadge :value="unread" severity="danger" :disabled="!unread">
                <Button
                    icon="pi pi-bell"
                    text
                    rounded
                    severity="secondary"
                    aria-label="Notificaciones"
                    v-tooltip.bottom="'Notificaciones'"
                    @click="toggleNotifications"
                />
            </OverlayBadge>

            <button type="button" class="profile-trigger" aria-label="Perfil" @click="toggleProfile">
                <Avatar :label="avatarLabel" shape="circle" class="profile-avatar" />
                <span class="profile-copy">
                    <strong>{{ adminName }}</strong>
                    <small>{{ adminEmail }}</small>
                </span>
                <i class="pi pi-chevron-down profile-chevron" aria-hidden="true"></i>
            </button>

            <Menu ref="profileMenu" :model="profileItems" popup />
        </div>

        <Popover ref="notificationsPanel" class="notifications-popover">
            <div class="notifications-panel">
                <div class="notifications-head">
                    <div>
                        <strong>Notificaciones</strong>
                        <small>{{ unread ? `${unread} sin leer` : 'Todo al día' }}</small>
                    </div>
                    <span class="notifications-count">{{ store.notifications.length }}</span>
                </div>

                <div v-if="recentNotifications.length" class="notifications-list">
                    <div
                        v-for="notification in recentNotifications"
                        :key="notification.id"
                        class="notification-item"
                        :class="{ unread: !notification.read, [notification.type]: true }"
                    >
                        <i :class="notificationIcon(notification.type)" aria-hidden="true"></i>
                        <div>
                            <strong>{{ notification.title }}</strong>
                            <p v-if="notification.detail">{{ notification.detail }}</p>
                            <small>{{ timeAgo(notification.at) }}</small>
                        </div>
                    </div>
                </div>

                <div v-else class="notifications-empty">
                    <i class="pi pi-bell-slash" aria-hidden="true"></i>
                    <strong>Sin notificaciones</strong>
                    <span>Las acciones que hagas en el panel van a aparecer acá.</span>
                </div>

                <div v-if="store.notifications.length" class="notifications-foot">
                    <Button label="Marcar leídas" text size="small" @click="markRead" />
                    <Button label="Limpiar" text size="small" severity="danger" @click="clearAll" />
                </div>
            </div>
        </Popover>
    </header>
</template>

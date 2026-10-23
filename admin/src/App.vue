<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import AppSidebar from './components/AppSidebar.vue';
import AppTopbar from './components/AppTopbar.vue';
import DashboardView from './components/DashboardView.vue';
import DocumentView from './components/DocumentView.vue';
import LoginView from './components/LoginView.vue';
import MaintenanceView from './components/MaintenanceView.vue';
import MigrationView from './components/MigrationView.vue';
import ResourceView from './components/ResourceView.vue';
import SettingsDialog from './components/SettingsDialog.vue';
import { DOCUMENTS, MODULES, NAV_SECTIONS, RESOURCES } from './resources';
import {
    applyPrefs,
    clearSession,
    store,
} from './store';

const toast = useToast();
const settingsVisible = ref(false);
const authExpired = ref(false);

const meta = computed(() => MODULES[store.view] || MODULES.dashboard);
const unread = computed(() => store.notifications.filter((notification) => !notification.read).length);

function viewFromHash() {
    const key = window.location.hash.replace(/^#\/?/, '').split('?')[0];

    return MODULES[key] ? key : 'dashboard';
}

function navigate(key) {
    if (!MODULES[key]) {
        key = 'dashboard';
    }

    store.view = key;
    store.sidebarOpen = false;

    if (window.location.hash !== `#/${key}`) {
        window.location.hash = `#/${key}`;
    }
}

function onHashChange() {
    store.view = viewFromHash();
}

function handleLoggedIn() {
    authExpired.value = false;
    navigate(viewFromHash());
}

function logout() {
    clearSession();
    store.view = 'dashboard';
    window.location.hash = '#/dashboard';
    toast.add({
        severity: 'info',
        summary: 'Sesión cerrada',
        detail: 'Hasta la próxima.',
        life: 2500,
    });
}

function onUnauthorized() {
    if (authExpired.value) {
        return;
    }

    authExpired.value = true;
    clearSession();
    toast.add({
        severity: 'error',
        summary: 'Sesión expirada',
        detail: 'Volvé a iniciar sesión para continuar.',
        life: 5000,
    });
}

function quickCreate(moduleKey) {
    if (!MODULES[moduleKey]) {
        return;
    }

    store.createRequest = moduleKey;
    navigate(moduleKey);
}

onMounted(() => {
    applyPrefs();
    store.view = viewFromHash();

    if (!window.location.hash) {
        window.history.replaceState(null, '', '#/dashboard');
    }

    window.addEventListener('hashchange', onHashChange);
    window.addEventListener('cms:unauthorized', onUnauthorized);
});

onBeforeUnmount(() => {
    window.removeEventListener('hashchange', onHashChange);
    window.removeEventListener('cms:unauthorized', onUnauthorized);
});
</script>

<template>
    <Toast position="top-right" />
    <ConfirmDialog />

    <LoginView v-if="!store.token" @logged-in="handleLoggedIn" />

    <div v-else class="app-shell">
        <AppSidebar
            :sections="NAV_SECTIONS"
            :current="store.view"
            :open="store.sidebarOpen"
            @navigate="navigate"
            @settings="settingsVisible = true"
            @close="store.sidebarOpen = false"
        />

        <button
            v-if="store.sidebarOpen"
            type="button"
            class="sidebar-overlay"
            aria-label="Cerrar menú"
            @click="store.sidebarOpen = false"
        ></button>

        <div class="main-shell">
            <AppTopbar
                :meta="meta"
                :unread="unread"
                @menu="store.sidebarOpen = true"
                @settings="settingsVisible = true"
                @logout="logout"
            />

            <main class="app-content">
                <DashboardView
                    v-if="store.view === 'dashboard'"
                    @navigate="navigate"
                    @create="quickCreate"
                />

                <MaintenanceView v-else-if="store.view === 'maintenance'" />

                <MigrationView v-else-if="store.view === 'migrations'" />

                <ResourceView
                    v-else-if="RESOURCES[store.view]"
                    :key="store.view"
                    :module-key="store.view"
                />

                <DocumentView
                    v-else-if="DOCUMENTS[store.view]"
                    :key="store.view"
                    :module-key="store.view"
                    @navigate="navigate"
                />
            </main>
        </div>
    </div>

    <SettingsDialog v-model:visible="settingsVisible" />
</template>

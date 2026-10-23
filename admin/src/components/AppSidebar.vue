<script setup>
import { computed } from 'vue';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import logoLight from '../assets/ms-matafuegos-light.png';
import { MODULES } from '../resources';
import { store } from '../store';
import { initials } from '../utils';

const props = defineProps({
    sections: {
        type: Array,
        required: true,
    },
    current: {
        type: String,
        required: true,
    },
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['navigate', 'settings', 'close']);

const resolvedSections = computed(() => props.sections.map((section) => ({
    ...section,
    modules: section.items.map((key) => MODULES[key]).filter(Boolean),
})));

const adminName = computed(() => store.admin?.name || store.admin?.email || 'Administrador');
const adminEmail = computed(() => store.admin?.email || 'admin@msmatafuegos.com');
const avatarLabel = computed(() => initials(adminName.value, adminEmail.value));
</script>

<template>
    <aside class="app-sidebar" :class="{ open }">
        <div class="sidebar-brand">
            <img :src="logoLight" alt="MS Matafuegos" class="sidebar-logo" />
            <div>
                <small>Gestión comercial</small>
            </div>
            <Button
                icon="pi pi-times"
                text
                rounded
                severity="secondary"
                class="sidebar-close"
                aria-label="Cerrar menú"
                @click="emit('close')"
            />
        </div>

        <nav class="sidebar-nav" aria-label="Módulos del panel">
            <div v-for="section in resolvedSections" :key="section.label" class="nav-section">
                <span class="nav-label">{{ section.label }}</span>
                <button
                    v-for="module in section.modules"
                    :key="module.key"
                    type="button"
                    class="nav-item"
                    :class="{ active: current === module.key }"
                    @click="emit('navigate', module.key)"
                >
                    <i :class="module.icon" aria-hidden="true"></i>
                    <span>{{ module.title }}</span>
                    <span v-if="module.key === 'inquiries'" class="nav-dot"></span>
                </button>
            </div>
        </nav>

        <div class="sidebar-footer">
            <button type="button" class="nav-item nav-settings" @click="emit('settings')">
                <i class="pi pi-cog" aria-hidden="true"></i>
                <span>Configuración</span>
            </button>

            <div class="sidebar-user">
                <Avatar :label="avatarLabel" shape="circle" class="sidebar-avatar" />
                <div>
                    <strong>{{ adminName }}</strong>
                    <small>{{ adminEmail }}</small>
                </div>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { Menu, MessageCircle, Phone, X } from '@lucide/vue';
import BrandLogo from './BrandLogo.vue';
import { business, navItems, whatsappUrl } from '@/data/site';

const route = useRoute();
const menuOpen = ref(false);

watch(
    () => route.fullPath,
    () => {
        menuOpen.value = false;
    }
);
</script>

<template>
    <div>
        <div class="bg-ink-950 text-white">
            <div class="container-page flex h-9 items-center justify-between gap-4 text-[11px] font-medium">
                <p class="truncate text-white/70">
                    Recarga, venta y mantenimiento de matafuegos en Tucumán
                </p>
                <div class="hidden items-center gap-5 sm:flex">
                    <span class="inline-flex items-center gap-1.5 text-white/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-safety-500"></span>
                        Servicio a domicilio
                    </span>
                    <a
                        :href="`tel:${business.phone}`"
                        class="inline-flex items-center gap-1.5 font-semibold text-white transition-colors hover:text-brand-300"
                    >
                        <Phone class="h-3 w-3" />
                        {{ business.phoneDisplay }}
                    </a>
                </div>
            </div>
        </div>

        <header
            class="sticky top-0 z-40 border-b border-ink-950/10 bg-paper/90 backdrop-blur-xl supports-[backdrop-filter]:bg-paper/80"
        >
            <div class="container-page flex h-[76px] items-center justify-between gap-5">
                <RouterLink to="/" class="shrink-0">
                    <BrandLogo />
                </RouterLink>

                <nav class="hidden items-center gap-1 lg:flex" aria-label="Navegación principal">
                    <RouterLink
                        v-for="item in navItems"
                        :key="item.to"
                        :to="item.to"
                        class="nav-link"
                        exact-active-class="nav-link-active"
                    >
                        {{ item.label }}
                    </RouterLink>
                </nav>

                <div class="flex items-center gap-2">
                    <a
                        :href="whatsappUrl()"
                        target="_blank"
                        rel="noopener"
                        class="btn-icon hidden border border-ink-200 bg-white text-ink-700 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 sm:inline-flex"
                        aria-label="Escribinos por WhatsApp"
                    >
                        <MessageCircle class="h-4 w-4" />
                    </a>

                    <RouterLink to="/consultas" class="btn-primary hidden px-5 sm:inline-flex">
                        Pedir presupuesto
                    </RouterLink>

                    <button
                        type="button"
                        class="btn-icon border border-ink-200 bg-white text-ink-950 lg:hidden"
                        :aria-expanded="menuOpen"
                        aria-controls="mobile-menu"
                        aria-label="Abrir menú"
                        @click="menuOpen = !menuOpen"
                    >
                        <X v-if="menuOpen" class="h-5 w-5" />
                        <Menu v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="menuOpen"
                    id="mobile-menu"
                    class="border-t border-ink-950/10 bg-paper px-4 pb-5 pt-3 lg:hidden"
                >
                    <nav class="mx-auto flex max-w-7xl flex-col" aria-label="Navegación móvil">
                        <RouterLink
                            v-for="item in navItems"
                            :key="item.to"
                            :to="item.to"
                            class="rounded-2xl px-4 py-3 text-base font-semibold text-ink-800 transition-colors hover:bg-white"
                            exact-active-class="bg-white text-brand-700 shadow-sm"
                        >
                            {{ item.label }}
                        </RouterLink>
                    </nav>
                    <div class="mx-auto mt-3 grid max-w-7xl gap-2 sm:grid-cols-2">
                        <RouterLink to="/consultas" class="btn-primary w-full">
                            Pedir presupuesto
                        </RouterLink>
                        <a :href="`tel:${business.phone}`" class="btn-light w-full">
                            <Phone class="h-4 w-4" />
                            {{ business.phoneDisplay }}
                        </a>
                    </div>
                </div>
            </Transition>
        </header>
    </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import SiteHeader from '@/components/SiteHeader.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import WhatsAppFloat from '@/components/WhatsAppFloat.vue';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-paper text-ink-950">
        <a
            href="#contenido"
            class="sr-only z-50 rounded-full bg-ink-950 px-5 py-3 text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4"
        >
            Saltar al contenido
        </a>

        <SiteHeader />

        <main id="contenido" class="flex-1">
            <RouterView v-slot="{ Component, route }">
                <Transition name="page" mode="out-in">
                    <component :is="Component" :key="route.path" />
                </Transition>
            </RouterView>
        </main>

        <SiteFooter />
        <WhatsAppFloat />
    </div>
</template>

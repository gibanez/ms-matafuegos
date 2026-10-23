<script setup>
import { ChevronRight } from '@lucide/vue';
import { RouterLink } from 'vue-router';

defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, required: true },
    lead: { type: String, default: '' },
    breadcrumb: { type: String, default: '' },
});
</script>

<template>
    <section class="relative overflow-hidden bg-ink-950 text-white">
        <div class="absolute inset-0 bg-grid opacity-60"></div>
        <div class="absolute -right-20 -top-28 h-80 w-80 rounded-full bg-brand-600/25 blur-3xl"></div>
        <div class="absolute -left-32 bottom-0 h-72 w-72 rounded-full bg-brand-600/10 blur-3xl"></div>

        <div class="container-page relative py-12 sm:py-16 lg:py-20">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-white/40" aria-label="Migas de pan">
                <RouterLink to="/" class="transition-colors hover:text-white">Inicio</RouterLink>
                <ChevronRight class="h-3.5 w-3.5" />
                <span class="text-white/70">{{ breadcrumb || title }}</span>
            </nav>

            <div class="mt-8 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p v-if="eyebrow" class="eyebrow-light">
                        <span class="h-px w-7 bg-brand-400"></span>
                        {{ eyebrow }}
                    </p>
                    <h1 class="mt-4 font-display text-5xl font-black uppercase leading-[0.9] sm:text-6xl lg:text-7xl">
                        {{ title }}
                    </h1>
                    <p v-if="lead" class="mt-5 max-w-2xl text-base leading-7 text-white/60 sm:text-lg">
                        {{ lead }}
                    </p>
                </div>

                <div v-if="$slots.actions" class="shrink-0">
                    <slot name="actions"></slot>
                </div>
            </div>
        </div>
    </section>
</template>

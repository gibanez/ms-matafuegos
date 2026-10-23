<script setup>
import { ArrowLeft, Home, MessageCircle } from '@lucide/vue';
import { RouterLink } from 'vue-router';
import { whatsappUrl } from '@/data/site';
</script>

<template>
    <section class="relative flex min-h-[70vh] items-center overflow-hidden bg-ink-950 py-20 text-white">
        <div class="absolute inset-0 bg-grid opacity-60"></div>
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-brand-600/25 blur-3xl"></div>

        <div class="container-page relative text-center">
            <p class="font-display text-[7rem] font-black leading-none text-brand-600 sm:text-[10rem]">404</p>
            <h1 class="mt-2 font-display text-4xl font-black uppercase sm:text-5xl">
                No encontramos esta página
            </h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-7 text-white/60">
                Puede que el enlace haya cambiado o que la página ya no exista. Volvé al inicio o
                escribinos y te ayudamos.
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <RouterLink to="/" class="btn-primary">
                    <Home class="h-4 w-4" />
                    Ir al inicio
                </RouterLink>
                <a :href="whatsappUrl()" target="_blank" rel="noopener" class="btn-ghost-light">
                    <MessageCircle class="h-4 w-4" />
                    Escribinos
                </a>
            </div>
            <RouterLink
                to="/productos"
                class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-white/50 transition hover:text-white"
            >
                <ArrowLeft class="h-4 w-4" />
                Ver todos los productos
            </RouterLink>
        </div>
    </section>
</template>

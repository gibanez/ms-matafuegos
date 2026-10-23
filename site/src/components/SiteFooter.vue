<script setup>
import { Clock, MapPin, MessageCircle, Phone } from '@lucide/vue';
import { RouterLink } from 'vue-router';
import BrandLogo from './BrandLogo.vue';
import { business, navItems, whatsappUrl } from '@/data/site';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="bg-ink-950 text-white">
        <div class="container-page pb-10 pt-16 lg:pt-20">
            <div class="grid gap-12 lg:grid-cols-[1.35fr_0.7fr_1fr]">
                <div>
                    <BrandLogo light />
                    <p class="mt-6 max-w-sm text-sm leading-7 text-white/60">
                        Todo lo que necesitás para habilitar y tener tu matafuego al día. Recarga,
                        venta, mantenimiento y asesoramiento en Tucumán.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a
                            :href="whatsappUrl()"
                            target="_blank"
                            rel="noopener"
                            class="btn-primary px-5 py-2.5"
                        >
                            <MessageCircle class="h-4 w-4" />
                            WhatsApp
                        </a>
                        <a
                            :href="`tel:${business.phone}`"
                            class="btn-ghost-light px-5 py-2.5"
                        >
                            <Phone class="h-4 w-4" />
                            Llamar
                        </a>
                    </div>
                </div>

                <div>
                    <h2 class="font-display text-lg font-bold uppercase tracking-[0.16em] text-white/80">
                        Navegación
                    </h2>
                    <ul class="mt-5 space-y-3 text-sm text-white/60">
                        <li v-for="item in navItems" :key="item.to">
                            <RouterLink
                                :to="item.to"
                                class="inline-flex items-center gap-2 transition-colors hover:text-white"
                            >
                                <span class="h-px w-3 bg-brand-500"></span>
                                {{ item.label }}
                            </RouterLink>
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-display text-lg font-bold uppercase tracking-[0.16em] text-white/80">
                        Contacto
                    </h2>
                    <ul class="mt-5 space-y-4 text-sm text-white/60">
                        <li class="flex gap-3">
                            <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                            <span>
                                {{ business.address }}<br />
                                {{ business.city }}, {{ business.province }}
                            </span>
                        </li>
                        <li class="flex gap-3">
                            <Phone class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                            <a :href="`tel:${business.phone}`" class="transition-colors hover:text-white">
                                {{ business.phoneDisplay }}
                            </a>
                        </li>
                        <li class="flex gap-3">
                            <Clock class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                            <span>{{ business.hours }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-14 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-white/60 sm:flex-row sm:items-center sm:justify-between"
            >
                <p>© {{ year }} {{ business.name }}. Todos los derechos reservados.</p>
                <p class="inline-flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-safety-500"></span>
                    Presupuesto sin cargo · Servicio a domicilio
                </p>
            </div>
        </div>
    </footer>
</template>

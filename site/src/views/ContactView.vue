<script setup>
import { Clock, ExternalLink, MapPin, MessageCircle, Navigation, Phone, Truck } from '@lucide/vue';
import { RouterLink } from 'vue-router';
import MapEmbed from '@/components/MapEmbed.vue';
import PageHero from '@/components/PageHero.vue';
import { business, whatsappUrl } from '@/data/site';
</script>

<template>
    <div>
        <PageHero
            eyebrow="Contacto"
            title="Estamos cerca"
            lead="Pasá por el local o coordinamos el servicio a domicilio. Te atendemos de lunes a sábados en horario comercial."
            breadcrumb="Contacto"
        >
            <template #actions>
                <a :href="whatsappUrl()" target="_blank" rel="noopener" class="btn-primary">
                    <MessageCircle class="h-4 w-4" />
                    Escribinos por WhatsApp
                </a>
            </template>
        </PageHero>

        <section class="py-12 sm:py-16 lg:py-20">
            <div class="container-page grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-stretch">
                <div class="flex flex-col gap-4">
                    <div class="card flex gap-4 p-5 sm:p-6">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                            <MapPin class="h-6 w-6" />
                        </span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-ink-400">Dirección</p>
                            <p class="mt-1 font-display text-2xl font-bold uppercase leading-none text-ink-950">
                                {{ business.address }}
                            </p>
                            <p class="mt-1 text-sm text-ink-500">
                                {{ business.city }}, {{ business.province }}
                            </p>
                            <a
                                :href="business.mapLink"
                                target="_blank"
                                rel="noopener"
                                class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800"
                            >
                                Cómo llegar
                                <ExternalLink class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <div class="card flex gap-4 p-5">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-ink-100 text-ink-700">
                                <Phone class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-ink-400">Teléfono</p>
                                <a
                                    :href="`tel:${business.phone}`"
                                    class="mt-1 block font-display text-2xl font-bold text-ink-950 hover:text-brand-700"
                                >
                                    {{ business.phoneDisplay }}
                                </a>
                            </div>
                        </div>

                        <div class="card flex gap-4 p-5">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-ink-100 text-ink-700">
                                <Clock class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-ink-400">Horarios</p>
                                <p class="mt-1 text-sm font-semibold leading-6 text-ink-950">
                                    {{ business.hours }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-3xl bg-brand-700 p-6 text-white">
                        <div class="absolute inset-0 bg-grid opacity-40"></div>
                        <div class="relative">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white/15">
                                <Truck class="h-5 w-5" />
                            </span>
                            <h2 class="mt-4 font-display text-3xl font-black uppercase leading-none">
                                ¿Necesitás servicio a domicilio?
                            </h2>
                            <p class="mt-3 text-sm leading-6 text-white/75">
                                Coordinamos la visita para comercios, consorcios, escuelas, galpones y
                                vehículos. Contanos dónde estás.
                            </p>
                            <a
                                :href="whatsappUrl('Hola MS Matafuegos, necesito coordinar un servicio a domicilio.')"
                                target="_blank"
                                rel="noopener"
                                class="btn mt-5 bg-white text-brand-800 hover:bg-brand-50"
                            >
                                <Navigation class="h-4 w-4" />
                                Coordinar visita
                            </a>
                        </div>
                    </div>
                </div>

                <div class="min-h-[520px]">
                    <MapEmbed height="h-full min-h-[520px]" />
                </div>
            </div>
        </section>

        <section class="border-t border-ink-950/5 bg-white py-14 lg:py-16">
            <div class="container-page flex flex-col items-start justify-between gap-6 rounded-3xl bg-ink-950 px-6 py-9 text-white sm:px-9 lg:flex-row lg:items-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-300">
                        Presupuesto sin cargo
                    </p>
                    <h2 class="mt-2 font-display text-3xl font-black uppercase leading-none sm:text-4xl">
                        ¿Te quedó alguna duda?
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/60">
                        Escribinos y te respondemos con la información que necesitás.
                    </p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a :href="`tel:${business.phone}`" class="btn-ghost-light">
                        <Phone class="h-4 w-4" />
                        Llamar
                    </a>
                    <RouterLink to="/consultas" class="btn-primary">Dejar una consulta</RouterLink>
                </div>
            </div>
        </section>
    </div>
</template>

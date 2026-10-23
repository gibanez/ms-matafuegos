<script setup>
import { onMounted, ref } from 'vue';
import { ArrowRight, Check, Clock, MapPin, MessageCircle, Phone, ShieldCheck, Truck } from '@lucide/vue';
import { RouterLink } from 'vue-router';
import ExtinguisherArt from '@/components/ExtinguisherArt.vue';
import MapEmbed from '@/components/MapEmbed.vue';
import ProductCard from '@/components/ProductCard.vue';
import SectionHeading from '@/components/SectionHeading.vue';
import ServiceIcon from '@/components/ServiceIcon.vue';
import { getProducts } from '@/api';
import { benefits, business, services, steps, whatsappUrl } from '@/data/site';

const featured = ref([]);
const loading = ref(true);
const error = ref('');

async function loadFeatured() {
    loading.value = true;
    error.value = '';

    try {
        let response = await getProducts({ featured: 1 });
        let products = response.products || [];

        if (!products.length) {
            response = await getProducts();
            products = response.products || [];
        }

        featured.value = products.slice(0, 4);
    } catch (loadError) {
        error.value = loadError.message;
    } finally {
        loading.value = false;
    }
}

onMounted(loadFeatured);
</script>

<template>
    <div>
        <section class="relative overflow-hidden bg-ink-950 text-white">
            <div class="absolute inset-0 bg-grid opacity-70"></div>
            <div class="absolute -left-40 top-24 h-96 w-96 rounded-full bg-brand-600/15 blur-3xl"></div>
            <div class="absolute -right-24 -top-20 h-[30rem] w-[30rem] rounded-full bg-brand-600/20 blur-3xl"></div>

            <div class="container-page relative grid gap-12 pb-14 pt-14 lg:grid-cols-[1.06fr_0.94fr] lg:items-center lg:gap-16 lg:pb-20 lg:pt-20">
                <div v-reveal>
                    <div class="inline-flex flex-wrap items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-white/75 backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-safety-500"></span>
                        Presupuesto sin cargo
                        <span class="h-3 w-px bg-white/15"></span>
                        Servicio a domicilio en Tucumán
                    </div>

                    <h1 class="mt-7 font-display text-[3.6rem] font-black uppercase leading-[0.88] sm:text-7xl lg:text-[5.2rem]">
                        Tu matafuego,
                        <span class="block text-brand-500">siempre listo.</span>
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-7 text-white/65 sm:text-lg sm:leading-8">
                        Hacemos la recarga de tu matafuego y te asesoramos para que tu negocio, casa,
                        vehículo, consorcio, galpón o escuela esté protegido y al día.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <RouterLink to="/consultas" class="btn-primary px-7 py-3.5">
                            Pedir presupuesto
                            <ArrowRight class="h-4 w-4" />
                        </RouterLink>
                        <RouterLink to="/productos" class="btn-ghost-light px-7 py-3.5">
                            Ver productos
                        </RouterLink>
                    </div>

                    <ul class="mt-8 grid gap-3 text-sm text-white/65 sm:grid-cols-2">
                        <li class="flex items-center gap-2.5">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-safety-500/15 text-safety-500">
                                <Check class="h-3.5 w-3.5" />
                            </span>
                            Recarga y mantenimiento
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-safety-500/15 text-safety-500">
                                <Check class="h-3.5 w-3.5" />
                            </span>
                            Venta de equipos y accesorios
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-safety-500/15 text-safety-500">
                                <Check class="h-3.5 w-3.5" />
                            </span>
                            Atención personalizada
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-safety-500/15 text-safety-500">
                                <Check class="h-3.5 w-3.5" />
                            </span>
                            Servicio a domicilio
                        </li>
                    </ul>
                </div>

                <div v-reveal="{ delay: 140 }" class="relative mx-auto w-full max-w-[480px] lg:max-w-none">
                    <ExtinguisherArt />
                </div>
            </div>

            <div class="container-page relative border-t border-white/10">
                <div class="grid divide-y divide-white/10 py-5 text-sm text-white/65 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    <div class="flex items-center gap-3 py-3 sm:py-0 sm:pr-6">
                        <MapPin class="h-4 w-4 shrink-0 text-brand-400" />
                        <span>{{ business.address }}, {{ business.city }}</span>
                    </div>
                    <div class="flex items-center gap-3 py-3 sm:px-6 sm:py-0">
                        <Clock class="h-4 w-4 shrink-0 text-brand-400" />
                        <span>{{ business.hours }}</span>
                    </div>
                    <div class="flex items-center gap-3 py-3 sm:pl-6 sm:py-0">
                        <Truck class="h-4 w-4 shrink-0 text-brand-400" />
                        <span>Servicio a domicilio</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 lg:py-28">
            <div class="container-page">
                <SectionHeading
                    eyebrow="Qué hacemos"
                    title="Todo para tu seguridad"
                    lead="Desde la recarga hasta los elementos que necesitás para habilitar tu espacio. Nos ocupamos de que no te falte nada."
                >
                    <template #actions>
                        <a :href="whatsappUrl()" target="_blank" rel="noopener" class="btn-light">
                            <MessageCircle class="h-4 w-4" />
                            Consultar por WhatsApp
                        </a>
                    </template>
                </SectionHeading>

                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <article
                        v-for="(service, index) in services"
                        :key="service.title"
                        v-reveal="{ delay: index * 80 }"
                        class="reveal group relative overflow-hidden rounded-3xl bg-white p-6 shadow-card ring-1 ring-ink-950/5 transition duration-300 hover:-translate-y-1 hover:shadow-soft"
                    >
                        <span class="absolute right-5 top-4 font-display text-4xl font-black text-ink-950/[0.04]">
                            0{{ index + 1 }}
                        </span>
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 transition group-hover:bg-brand-600 group-hover:text-white">
                            <ServiceIcon :name="service.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-6 font-display text-2xl font-bold uppercase leading-none text-ink-950">
                            {{ service.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-ink-500">
                            {{ service.description }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="border-y border-ink-950/5 bg-white py-20 lg:py-28">
            <div class="container-page">
                <SectionHeading
                    eyebrow="Catálogo"
                    title="Productos destacados"
                    lead="Equipos y elementos de seguridad seleccionados para tu casa, vehículo o negocio."
                >
                    <template #actions>
                        <RouterLink to="/productos" class="btn-dark">
                            Ver catálogo completo
                            <ArrowRight class="h-4 w-4" />
                        </RouterLink>
                    </template>
                </SectionHeading>

                <div v-if="loading" class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="index in 4"
                        :key="index"
                        class="h-[430px] animate-pulse rounded-3xl bg-ink-100"
                    ></div>
                </div>

                <div
                    v-else-if="error"
                    class="mt-12 flex flex-col items-start justify-between gap-4 rounded-3xl border border-brand-200 bg-brand-50 p-6 sm:flex-row sm:items-center"
                >
                    <div>
                        <p class="font-semibold text-brand-900">No pudimos cargar los productos.</p>
                        <p class="mt-1 text-sm text-brand-800/80">{{ error }}</p>
                    </div>
                    <button type="button" class="btn-primary" @click="loadFeatured">Reintentar</button>
                </div>

                <div v-else-if="featured.length" class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <ProductCard
                        v-for="(product, index) in featured"
                        :key="product.id"
                        :product="product"
                        :reveal-delay="index * 70"
                    />
                </div>

                <div
                    v-else
                    class="mt-12 flex flex-col items-center rounded-3xl border border-dashed border-ink-200 bg-paper px-6 py-14 text-center"
                >
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                        <ShieldCheck class="h-7 w-7" />
                    </span>
                    <h3 class="mt-5 font-display text-3xl font-bold uppercase text-ink-950">
                        Estamos preparando el catálogo
                    </h3>
                    <p class="mt-2 max-w-md text-sm leading-6 text-ink-500">
                        Mientras tanto, contanos qué necesitás y te pasamos una propuesta sin cargo.
                    </p>
                    <RouterLink to="/consultas" class="btn-primary mt-6">Pedir presupuesto</RouterLink>
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden bg-ink-950 py-20 text-white lg:py-28">
            <div class="absolute inset-0 bg-grid opacity-60"></div>
            <div class="absolute -right-40 top-0 h-96 w-96 rounded-full bg-brand-600/20 blur-3xl"></div>

            <div class="container-page relative grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div v-reveal>
                    <p class="eyebrow-light">
                        <span class="h-px w-7 bg-brand-400"></span>
                        Por qué elegirnos
                    </p>
                    <h2 class="mt-4 section-title-light">
                        Seguridad que
                        <span class="text-brand-500">se nota.</span>
                    </h2>
                    <p class="mt-5 max-w-lg text-base leading-7 text-white/60">
                        No se trata solo de tener un matafuego: se trata de que esté en condiciones,
                        en el lugar correcto y listo para responder cuando hace falta.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a :href="whatsappUrl()" target="_blank" rel="noopener" class="btn-primary">
                            <MessageCircle class="h-4 w-4" />
                            Coordinar servicio
                        </a>
                        <a :href="`tel:${business.phone}`" class="btn-ghost-light">
                            <Phone class="h-4 w-4" />
                            {{ business.phoneDisplay }}
                        </a>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div
                        v-for="(benefit, index) in benefits"
                        :key="benefit"
                        v-reveal="{ delay: index * 80 }"
                        class="reveal flex gap-4 rounded-3xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur"
                    >
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-600 text-white">
                            <Check class="h-4 w-4" />
                        </span>
                        <p class="text-sm leading-6 text-white/75">{{ benefit }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 lg:py-28">
            <div class="container-page">
                <SectionHeading
                    eyebrow="Cómo trabajamos"
                    title="Simple, rápido y sin vueltas"
                    lead="Nos contás qué necesitás, te pasamos un presupuesto y coordinamos el servicio."
                />

                <div class="relative mt-12 grid gap-4 lg:grid-cols-3">
                    <div class="absolute left-[12%] right-[12%] top-10 hidden border-t border-dashed border-ink-300 lg:block"></div>
                    <article
                        v-for="(step, index) in steps"
                        :key="step.number"
                        v-reveal="{ delay: index * 100 }"
                        class="reveal relative rounded-3xl bg-white p-6 shadow-card ring-1 ring-ink-950/5 sm:p-8"
                    >
                        <span class="relative z-10 grid h-14 w-14 place-items-center rounded-2xl bg-ink-950 font-display text-2xl font-black text-white">
                            {{ step.number }}
                        </span>
                        <h3 class="mt-6 font-display text-2xl font-bold uppercase text-ink-950">
                            {{ step.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-ink-500">{{ step.description }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="pb-20 lg:pb-28">
            <div class="container-page">
                <div class="relative overflow-hidden rounded-[2rem] bg-brand-700 px-6 py-12 text-white shadow-soft sm:px-10 lg:flex lg:items-center lg:justify-between lg:px-14 lg:py-14">
                    <div class="absolute inset-0 bg-grid opacity-40"></div>
                    <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="relative max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-white/65">
                            Presupuesto sin cargo
                        </p>
                        <h2 class="mt-3 font-display text-4xl font-black uppercase leading-[0.92] sm:text-5xl">
                            ¿Recargamos tu matafuego?
                        </h2>
                        <p class="mt-4 max-w-xl text-sm leading-7 text-white/75 sm:text-base">
                            Escribinos con la cantidad de equipos y el lugar donde estás. Te respondemos
                            con la mejor opción y coordinamos el servicio.
                        </p>
                    </div>
                    <div class="relative mt-7 flex flex-col gap-3 sm:flex-row lg:mt-0 lg:shrink-0">
                        <RouterLink to="/consultas" class="btn bg-white text-brand-800 hover:bg-brand-50">
                            Completar consulta
                        </RouterLink>
                        <a :href="whatsappUrl()" target="_blank" rel="noopener" class="btn-ghost-light border-white/30">
                            <MessageCircle class="h-4 w-4" />
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-t border-ink-950/5 bg-white py-20 lg:py-28">
            <div class="container-page grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-stretch">
                <div v-reveal class="flex flex-col justify-between rounded-3xl bg-ink-950 p-7 text-white sm:p-9">
                    <div>
                        <p class="eyebrow-light">
                            <span class="h-px w-7 bg-brand-400"></span>
                            Dónde estamos
                        </p>
                        <h2 class="mt-4 font-display text-4xl font-black uppercase leading-none sm:text-5xl">
                            Pasá por el local
                        </h2>
                        <p class="mt-4 text-sm leading-7 text-white/60">
                            Estamos en {{ business.city }}. También coordinamos servicio a domicilio para
                            comercios, consorcios, escuelas, galpones y vehículos.
                        </p>
                    </div>

                    <ul class="mt-8 space-y-4 text-sm text-white/70">
                        <li class="flex gap-3">
                            <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                            <span>{{ business.address }}<br />{{ business.city }}, {{ business.province }}</span>
                        </li>
                        <li class="flex gap-3">
                            <Clock class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                            <span>{{ business.hours }}</span>
                        </li>
                        <li class="flex gap-3">
                            <Phone class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                            <a :href="`tel:${business.phone}`" class="font-semibold text-white hover:text-brand-300">
                                {{ business.phoneDisplay }}
                            </a>
                        </li>
                    </ul>

                    <RouterLink to="/contacto" class="btn-primary mt-8 w-full sm:w-fit">
                        Ver ubicación y contacto
                        <ArrowRight class="h-4 w-4" />
                    </RouterLink>
                </div>

                <div v-reveal="{ delay: 100 }" class="reveal min-h-[390px]">
                    <MapEmbed />
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { ArrowLeft, Check, ChevronRight, MessageCircle, PackageCheck, ShieldCheck, Truck } from '@lucide/vue';
import { RouterLink, useRoute } from 'vue-router';
import ProductArtwork from '@/components/ProductArtwork.vue';
import ProductCard from '@/components/ProductCard.vue';
import { getProduct, getProducts } from '@/api';
import { whatsappUrl } from '@/data/site';
import { formatMoney } from '@/utils';

const route = useRoute();
const product = ref(null);
const related = ref([]);
const selectedMedia = ref(null);
const loading = ref(true);
const error = ref('');

const price = computed(() => formatMoney(product.value?.price));
const mediaItems = computed(() => product.value?.media || []);
const productUrlMessage = computed(() =>
    whatsappUrl(
        `Hola MS Matafuegos, quiero consultar por "${product.value?.name}". ¿Me pasás más información?`
    )
);

async function loadProduct(slug) {
    loading.value = true;
    error.value = '';
    product.value = null;
    selectedMedia.value = null;
    related.value = [];

    try {
        const response = await getProduct(slug);
        product.value = response;
        selectedMedia.value = mediaItems.value[0] || null;

        if (response.categoryId) {
            getProducts({ categoryId: response.categoryId })
                .then((relatedResponse) => {
                    related.value = (relatedResponse.products || [])
                        .filter((item) => item.id !== response.id)
                        .slice(0, 3);
                })
                .catch(() => {
                    related.value = [];
                });
        }
    } catch (loadError) {
        error.value =
            loadError.status === 404
                ? 'El producto que buscás no existe o ya no está disponible.'
                : loadError.message;
    } finally {
        loading.value = false;
    }
}

watch(
    () => route.params.slug,
    (slug) => {
        if (slug) {
            loadProduct(String(slug));
        }
    },
    { immediate: true }
);
</script>

<template>
    <div>
        <section class="border-b border-ink-950/5 bg-white">
            <div class="container-page flex flex-wrap items-center gap-2 py-5 text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">
                <RouterLink to="/" class="transition-colors hover:text-ink-950">Inicio</RouterLink>
                <ChevronRight class="h-3.5 w-3.5" />
                <RouterLink to="/productos" class="transition-colors hover:text-ink-950">Productos</RouterLink>
                <ChevronRight class="h-3.5 w-3.5" />
                <span class="text-ink-700">{{ product?.name || 'Detalle' }}</span>
            </div>
        </section>

        <section class="py-10 sm:py-14 lg:py-16">
            <div class="container-page">
                <div v-if="loading" class="grid gap-10 lg:grid-cols-2">
                    <div class="aspect-[4/3] animate-pulse rounded-3xl bg-ink-100"></div>
                    <div class="space-y-4">
                        <div class="h-4 w-28 animate-pulse rounded bg-ink-100"></div>
                        <div class="h-14 w-3/4 animate-pulse rounded bg-ink-100"></div>
                        <div class="h-24 w-full animate-pulse rounded bg-ink-100"></div>
                        <div class="h-12 w-40 animate-pulse rounded bg-ink-100"></div>
                    </div>
                </div>

                <div
                    v-else-if="error"
                    class="mx-auto flex max-w-xl flex-col items-center rounded-3xl border border-dashed border-ink-200 bg-white px-6 py-16 text-center"
                >
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                        <PackageCheck class="h-7 w-7" />
                    </span>
                    <h1 class="mt-5 font-display text-3xl font-bold uppercase text-ink-950">
                        Producto no disponible
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-ink-500">{{ error }}</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <RouterLink to="/productos" class="btn-dark">
                            <ArrowLeft class="h-4 w-4" />
                            Volver al catálogo
                        </RouterLink>
                        <RouterLink to="/consultas" class="btn-primary">Hacer una consulta</RouterLink>
                    </div>
                </div>

                <div v-else-if="product" class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:gap-14">
                    <div>
                        <div class="overflow-hidden rounded-3xl bg-sand-100 shadow-card ring-1 ring-ink-950/5">
                            <div class="relative aspect-[4/3]">
                                <video
                                    v-if="selectedMedia?.type === 'video'"
                                    :src="selectedMedia.url"
                                    class="h-full w-full object-cover"
                                    controls
                                    playsinline
                                ></video>
                                <img
                                    v-else-if="selectedMedia?.type === 'image'"
                                    :src="selectedMedia.url"
                                    :alt="product.name"
                                    class="h-full w-full object-cover"
                                />
                                <ProductArtwork v-else />
                            </div>
                        </div>

                        <div v-if="mediaItems.length > 1" class="mt-4 flex gap-3 overflow-x-auto pb-1">
                            <button
                                v-for="media in mediaItems"
                                :key="media.id"
                                type="button"
                                class="relative h-20 w-24 shrink-0 overflow-hidden rounded-2xl bg-ink-100 ring-2 transition"
                                :class="selectedMedia?.id === media.id ? 'ring-brand-600' : 'ring-transparent hover:ring-ink-300'"
                                :aria-label="`Ver ${media.originalFilename}`"
                                @click="selectedMedia = media"
                            >
                                <img
                                    v-if="media.type === 'image'"
                                    :src="media.url"
                                    alt=""
                                    class="h-full w-full object-cover"
                                />
                                <video v-else :src="media.url" class="h-full w-full object-cover" muted></video>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-700">
                            {{ product.category?.name || 'Matafuegos' }}
                        </p>
                        <h1 class="mt-3 font-display text-5xl font-black uppercase leading-[0.92] text-ink-950 sm:text-6xl">
                            {{ product.name }}
                        </h1>

                        <div v-if="product.sku" class="mt-3 text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">
                            Código: {{ product.sku }}
                        </div>

                        <p class="mt-6 text-base leading-7 text-ink-600">
                            {{ product.description || 'Consultá con nuestro equipo por las características, medidas y disponibilidad de este producto.' }}
                        </p>

                        <div class="mt-7 flex flex-wrap items-end gap-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-ink-400">Precio</p>
                                <p class="mt-1 font-display text-4xl font-black leading-none text-ink-950">
                                    {{ price || 'Consultar' }}
                                </p>
                            </div>
                            <span
                                class="badge"
                                :class="product.inStock
                                    ? 'bg-safety-500/10 text-safety-600'
                                    : 'bg-brand-50 text-brand-700'"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full"
                                    :class="product.inStock ? 'bg-safety-500' : 'bg-brand-600'"
                                ></span>
                                {{ product.inStock ? 'Disponible' : 'Consultar stock' }}
                            </span>
                        </div>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a :href="productUrlMessage" target="_blank" rel="noopener" class="btn-primary px-7">
                                <MessageCircle class="h-4 w-4" />
                                Consultar por WhatsApp
                            </a>
                            <RouterLink to="/consultas" class="btn-light px-7">
                                Pedir presupuesto
                            </RouterLink>
                        </div>

                        <div class="mt-8 grid gap-3 border-t border-ink-200 pt-7 sm:grid-cols-3">
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-ink-100 text-ink-700">
                                    <ShieldCheck class="h-4 w-4" />
                                </span>
                                <p class="text-xs font-semibold leading-5 text-ink-600">Asesoramiento personalizado</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-ink-100 text-ink-700">
                                    <Truck class="h-4 w-4" />
                                </span>
                                <p class="text-xs font-semibold leading-5 text-ink-600">Servicio a domicilio</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-ink-100 text-ink-700">
                                    <Check class="h-4 w-4" />
                                </span>
                                <p class="text-xs font-semibold leading-5 text-ink-600">Presupuesto sin cargo</p>
                            </div>
                        </div>

                        <div class="mt-8 rounded-2xl bg-sand-100 p-5 text-sm leading-6 text-ink-600">
                            <strong class="text-ink-950">¿No sabés cuál elegir?</strong>
                            Contanos para qué espacio lo necesitás y te orientamos sin compromiso.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="related.length" class="border-t border-ink-950/5 bg-white py-16 lg:py-20">
            <div class="container-page">
                <div class="flex flex-wrap items-end justify-between gap-5">
                    <div>
                        <p class="eyebrow">
                            <span class="h-px w-7 bg-brand-600"></span>
                            Seguí explorando
                        </p>
                        <h2 class="mt-3 font-display text-4xl font-black uppercase text-ink-950 sm:text-5xl">
                            También te puede interesar
                        </h2>
                    </div>
                    <RouterLink to="/productos" class="btn-light">Ver todo</RouterLink>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <ProductCard
                        v-for="(item, index) in related"
                        :key="item.id"
                        :product="item"
                        :reveal-delay="index * 70"
                    />
                </div>
            </div>
        </section>
    </div>
</template>

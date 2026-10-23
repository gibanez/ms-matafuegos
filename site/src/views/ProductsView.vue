<script setup>
import { computed, onMounted, ref } from 'vue';
import { ArrowRight, MessageCircle, Search, SlidersHorizontal, X } from '@lucide/vue';
import { RouterLink } from 'vue-router';
import PageHero from '@/components/PageHero.vue';
import ProductCard from '@/components/ProductCard.vue';
import { getCategories, getProducts } from '@/api';
import { whatsappUrl } from '@/data/site';

const products = ref([]);
const categories = ref([]);
const query = ref('');
const selectedCategory = ref('all');
const loading = ref(true);
const error = ref('');

const filteredProducts = computed(() => {
    const search = query.value.trim().toLowerCase();

    return products.value
        .filter((product) => {
            const matchesCategory =
                selectedCategory.value === 'all' ||
                product.categoryId === selectedCategory.value;
            const haystack = [
                product.name,
                product.description,
                product.category?.name,
                product.sku,
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return matchesCategory && (!search || haystack.includes(search));
        })
        .sort((a, b) => {
            if (a.featured !== b.featured) {
                return a.featured ? -1 : 1;
            }

            return a.name.localeCompare(b.name, 'es');
        });
});

const hasFilters = computed(
    () => query.value.trim() !== '' || selectedCategory.value !== 'all'
);

async function loadData() {
    loading.value = true;
    error.value = '';

    try {
        const response = await getProducts();
        products.value = response.products || [];
    } catch (loadError) {
        error.value = loadError.message;
    } finally {
        loading.value = false;
    }
}

async function loadCategories() {
    try {
        const response = await getCategories();
        categories.value = response.categories || [];
    } catch {
        categories.value = [];
    }
}

function clearFilters() {
    query.value = '';
    selectedCategory.value = 'all';
}

onMounted(() => {
    loadData();
    loadCategories();
});
</script>

<template>
    <div>
        <PageHero
            eyebrow="Catálogo"
            title="Productos y servicios"
            lead="Matafuegos, recargas y elementos de seguridad. Si no encontrás lo que buscás, escribinos y te ayudamos."
            breadcrumb="Productos"
        >
            <template #actions>
                <a :href="whatsappUrl()" target="_blank" rel="noopener" class="btn-ghost-light">
                    <MessageCircle class="h-4 w-4" />
                    Consultar disponibilidad
                </a>
            </template>
        </PageHero>

        <section class="py-10 sm:py-14 lg:py-16">
            <div class="container-page">
                <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-700">
                            Explorá el catálogo
                        </p>
                        <h2 class="mt-2 font-display text-4xl font-black uppercase text-ink-950 sm:text-5xl">
                            Elegí lo que necesitás
                        </h2>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <label class="relative block sm:w-80">
                            <span class="sr-only">Buscar productos</span>
                            <Search class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                            <input
                                v-model="query"
                                type="search"
                                class="field-input pl-11 pr-11"
                                placeholder="Buscar por nombre o categoría..."
                            />
                            <button
                                v-if="query"
                                type="button"
                                class="absolute right-3 top-1/2 grid h-7 w-7 -translate-y-1/2 place-items-center rounded-full text-ink-400 transition hover:bg-ink-100 hover:text-ink-700"
                                aria-label="Limpiar búsqueda"
                                @click="query = ''"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-b border-ink-200 pb-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="scrollbar-thin flex gap-2 overflow-x-auto pb-1">
                        <button
                            type="button"
                            class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                            :class="selectedCategory === 'all'
                                ? 'bg-ink-950 text-white'
                                : 'bg-white text-ink-600 ring-1 ring-ink-200 hover:text-ink-950'"
                            @click="selectedCategory = 'all'"
                        >
                            Todos
                        </button>
                        <button
                            v-for="category in categories"
                            :key="category.id"
                            type="button"
                            class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                            :class="selectedCategory === category.id
                                ? 'bg-ink-950 text-white'
                                : 'bg-white text-ink-600 ring-1 ring-ink-200 hover:text-ink-950'"
                            @click="selectedCategory = category.id"
                        >
                            {{ category.name }}
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-sm text-ink-500 sm:justify-end">
                        <span class="inline-flex items-center gap-2">
                            <SlidersHorizontal class="h-4 w-4" />
                            {{ filteredProducts.length }}
                            {{ filteredProducts.length === 1 ? 'producto' : 'productos' }}
                        </span>
                        <button
                            v-if="hasFilters"
                            type="button"
                            class="font-semibold text-brand-700 underline-offset-4 hover:underline"
                            @click="clearFilters"
                        >
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <div v-if="loading" class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="index in 8"
                        :key="index"
                        class="h-[430px] animate-pulse rounded-3xl bg-ink-100"
                    ></div>
                </div>

                <div
                    v-else-if="error"
                    class="mt-8 flex flex-col items-start justify-between gap-4 rounded-3xl border border-brand-200 bg-brand-50 p-6 sm:flex-row sm:items-center"
                >
                    <div>
                        <p class="font-semibold text-brand-900">No pudimos cargar el catálogo.</p>
                        <p class="mt-1 text-sm text-brand-800/80">{{ error }}</p>
                    </div>
                    <button type="button" class="btn-primary" @click="loadData">Reintentar</button>
                </div>

                <div v-else-if="filteredProducts.length" class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <ProductCard
                        v-for="(product, index) in filteredProducts"
                        :key="product.id"
                        :product="product"
                        :reveal-delay="(index % 4) * 60"
                    />
                </div>

                <div
                    v-else
                    class="mt-8 flex flex-col items-center rounded-3xl border border-dashed border-ink-200 bg-white px-6 py-16 text-center"
                >
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-ink-100 text-ink-500">
                        <Search class="h-7 w-7" />
                    </span>
                    <h3 class="mt-5 font-display text-3xl font-bold uppercase text-ink-950">
                        No encontramos resultados
                    </h3>
                    <p class="mt-2 max-w-md text-sm leading-6 text-ink-500">
                        Probá con otra búsqueda o contanos qué necesitás. Seguro podemos ayudarte.
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <button v-if="hasFilters" type="button" class="btn-light" @click="clearFilters">
                            Limpiar filtros
                        </button>
                        <RouterLink to="/consultas" class="btn-primary">
                            Hacer una consulta
                            <ArrowRight class="h-4 w-4" />
                        </RouterLink>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

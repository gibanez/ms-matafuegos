<script setup>
import { ref, computed } from 'vue';
import { ArrowUpRight, MessageCircle } from '@lucide/vue';
import { RouterLink } from 'vue-router';
import ProductArtwork from './ProductArtwork.vue';
import { formatMoney } from '@/utils';
import { whatsappUrl } from '@/data/site';

const props = defineProps({
    product: { type: Object, required: true },
    revealDelay: { type: Number, default: 0 },
});

const imageFailed = ref(false);

const image = computed(() => {
    if (imageFailed.value) {
        return null;
    }

    return (props.product.media || []).find((item) => item.type === 'image') || null;
});

const price = computed(() => formatMoney(props.product.price));
const categoryName = computed(() => props.product.category?.name || 'Matafuegos');
const productUrl = computed(() => `/productos/${props.product.slug}`);
const inquiryUrl = computed(() =>
    whatsappUrl(
        `Hola MS Matafuegos, quiero consultar por "${props.product.name}". ¿Me pasás más información?`
    )
);
</script>

<template>
    <article
        v-reveal="{ delay: revealDelay }"
        class="reveal group flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-card ring-1 ring-ink-950/5 transition duration-300 hover:-translate-y-1 hover:shadow-soft"
    >
        <RouterLink
            :to="productUrl"
            class="relative block aspect-[4/3] overflow-hidden bg-sand-100"
            :aria-label="`Ver ${product.name}`"
        >
            <img
                v-if="image"
                :src="image.url"
                :alt="product.name"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                loading="lazy"
                @error="imageFailed = true"
            />
            <ProductArtwork v-else />

            <div class="absolute left-3 top-3 z-20 flex flex-wrap gap-2">
                <span v-if="product.featured" class="badge bg-ink-950 text-white">
                    Destacado
                </span>
                <span v-if="!product.inStock" class="badge bg-white/90 text-ink-700 backdrop-blur">
                    Sin stock
                </span>
            </div>
        </RouterLink>

        <div class="flex flex-1 flex-col p-5 sm:p-6">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-700">
                {{ categoryName }}
            </p>
            <h3 class="mt-2 font-display text-2xl font-bold uppercase leading-none text-ink-950">
                <RouterLink :to="productUrl" class="transition-colors hover:text-brand-700">
                    {{ product.name }}
                </RouterLink>
            </h3>
            <p class="mt-3 line-clamp-2 text-sm leading-6 text-ink-500">
                {{ product.description || 'Consultá disponibilidad y características con nuestro equipo.' }}
            </p>

            <div class="mt-auto flex items-end justify-between gap-4 pt-6">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-ink-400">
                        {{ price ? 'Precio' : 'Precio' }}
                    </p>
                    <p class="mt-1 font-display text-2xl font-bold leading-none text-ink-950">
                        {{ price || 'Consultar' }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        :href="inquiryUrl"
                        target="_blank"
                        rel="noopener"
                        class="btn-icon border border-ink-200 bg-white text-ink-600 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"
                        :aria-label="`Consultar por ${product.name} por WhatsApp`"
                    >
                        <MessageCircle class="h-4 w-4" />
                    </a>
                    <RouterLink
                        :to="productUrl"
                        class="btn-icon bg-ink-950 text-white hover:bg-brand-600"
                        :aria-label="`Ver detalle de ${product.name}`"
                    >
                        <ArrowUpRight class="h-4 w-4" />
                    </RouterLink>
                </div>
            </div>
        </div>
    </article>
</template>

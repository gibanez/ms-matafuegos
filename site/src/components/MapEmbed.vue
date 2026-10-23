<script setup>
import { ExternalLink, MapPin } from '@lucide/vue';
import { business } from '@/data/site';

defineProps({
    height: { type: String, default: 'h-full min-h-[360px]' },
});
</script>

<template>
    <div class="group relative overflow-hidden rounded-3xl bg-ink-100 ring-1 ring-ink-950/10" :class="height">
        <iframe
            :src="business.mapEmbed"
            title="Ubicación de MS Matafuegos en Av. Juan B. Justo 2665, Tucumán"
            class="absolute inset-0 h-full w-full grayscale-[25%] transition duration-500 group-hover:grayscale-0"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen
        ></iframe>

        <a
            :href="business.mapLink"
            target="_blank"
            rel="noopener"
            class="absolute bottom-4 left-4 right-4 flex items-center justify-between gap-3 rounded-2xl bg-white/95 px-4 py-3 text-sm font-semibold text-ink-950 shadow-card backdrop-blur transition hover:bg-white sm:right-auto sm:min-w-[300px]"
        >
            <span class="inline-flex items-center gap-2">
                <MapPin class="h-4 w-4 text-brand-600" />
                {{ business.address }}
            </span>
            <ExternalLink class="h-4 w-4 text-ink-400" />
        </a>
    </div>
</template>

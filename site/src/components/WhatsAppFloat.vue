<script setup>
import { MessageCircle } from '@lucide/vue';
import { whatsappUrl } from '@/data/site';
</script>

<template>
    <a
        :href="whatsappUrl()"
        target="_blank"
        rel="noopener"
        class="group fixed bottom-5 right-5 z-50 inline-flex items-center gap-3 rounded-full bg-[#25D366] py-3 pl-3 pr-4 text-sm font-bold text-[#073b1e] shadow-[0_18px_38px_-12px_rgba(37,211,102,0.7)] transition-transform hover:-translate-y-1 sm:bottom-7 sm:right-7"
        aria-label="Escribinos por WhatsApp"
    >
        <span class="relative grid h-9 w-9 place-items-center rounded-full bg-white/30">
            <span class="absolute inset-0 animate-pulse-ring rounded-full bg-white/40"></span>
            <MessageCircle class="relative h-5 w-5" />
        </span>
        <span class="hidden sm:inline">Escribinos</span>
    </a>
</template>

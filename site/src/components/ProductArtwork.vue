<script setup>
import { useId } from 'vue';

defineProps({
    tone: { type: String, default: 'light' },
});

const gradientId = `product-tank-${useId().replace(/[^a-zA-Z0-9_-]/g, '')}`;
</script>

<template>
    <div
        class="relative flex h-full w-full items-center justify-center overflow-hidden"
        :class="tone === 'dark'
            ? 'bg-ink-950'
            : 'bg-gradient-to-br from-sand-100 via-paper to-white'"
    >
        <div class="absolute inset-0 bg-grid-dark opacity-50"></div>
        <div
            class="absolute -right-8 -top-10 h-40 w-40 rounded-full blur-2xl"
            :class="tone === 'dark' ? 'bg-brand-600/25' : 'bg-brand-500/10'"
        ></div>

        <svg
            viewBox="0 0 150 210"
            class="relative z-10 h-[74%] w-auto drop-shadow-[0_18px_18px_rgba(11,15,20,0.28)]"
            aria-hidden="true"
        >
            <defs>
                <linearGradient :id="gradientId" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#ff6262" />
                    <stop offset="45%" stop-color="#dc1a23" />
                    <stop offset="100%" stop-color="#890b14" />
                </linearGradient>
            </defs>

            <ellipse cx="77" cy="197" rx="52" ry="8" fill="#0b0f14" opacity="0.18" />

            <path
                d="M54 42h45c11 0 20 9 20 20v104c0 18-15 33-33 33H66c-18 0-32-15-32-33V62c0-11 9-20 20-20Z"
                :fill="`url(#${gradientId})`"
            />
            <path d="M48 43h13v150c0 4-3 7-7 7-7 0-12-6-12-13V57c0-7 2-12 6-14Z" fill="#fff" opacity="0.16" />
            <rect x="37" y="172" width="80" height="9" rx="4.5" fill="#720910" />
            <rect x="46" y="181" width="62" height="6" rx="3" fill="#4d060b" />

            <rect x="62" y="29" width="29" height="22" rx="5" fill="#1b222a" />
            <rect x="57" y="46" width="39" height="7" rx="3.5" fill="#0b0f14" />
            <rect x="56" y="23" width="41" height="8" rx="4" fill="#1b222a" />
            <path d="M68 18h17c4 0 7 3 7 7H61c0-4 3-7 7-7Z" fill="#3c4651" />

            <path
                d="M100 39c26 1 39 21 33 45-4 16-13 26-27 32"
                fill="none"
                stroke="#0b0f14"
                stroke-width="11"
                stroke-linecap="round"
            />
            <path
                d="M100 39c26 1 39 21 33 45-4 16-13 26-27 32"
                fill="none"
                stroke="#2a323c"
                stroke-width="5"
                stroke-linecap="round"
            />
            <rect x="102" y="108" width="24" height="10" rx="4" fill="#0b0f14" transform="rotate(22 102 108)" />

            <circle cx="109" cy="47" r="12" fill="#0b0f14" />
            <circle cx="109" cy="47" r="8.5" fill="#dbeafe" opacity="0.8" />
            <path d="M109 38.5a8.5 8.5 0 0 1 7.5 12.5L109 47Z" fill="#16a34a" />
            <circle cx="109" cy="47" r="2" fill="#0b0f14" />

            <rect x="49" y="95" width="58" height="66" rx="8" fill="#f7f5f0" />
            <text
                x="78"
                y="122"
                text-anchor="middle"
                font-family="Barlow Condensed, Arial Narrow, sans-serif"
                font-size="25"
                font-weight="900"
                fill="#b60f19"
            >
                MS
            </text>
            <text
                x="78"
                y="135"
                text-anchor="middle"
                font-family="Inter, Arial, sans-serif"
                font-size="4.7"
                font-weight="800"
                fill="#151a20"
                letter-spacing="1.1"
            >
                MATAFUEGOS
            </text>
            <rect x="62" y="142" width="32" height="10" rx="5" fill="#12813b" />
            <text
                x="78"
                y="149.4"
                text-anchor="middle"
                font-family="Inter, Arial, sans-serif"
                font-size="5"
                font-weight="800"
                fill="#fff"
                letter-spacing="0.8"
            >
                ABC
            </text>
            <rect x="58" y="155" width="40" height="2" rx="1" fill="#d3d7dc" />
        </svg>

        <span
            class="absolute bottom-3 right-4 font-display text-4xl font-black uppercase tracking-tight"
            :class="tone === 'dark' ? 'text-white/10' : 'text-ink-950/5'"
        >
            MS
        </span>
    </div>
</template>

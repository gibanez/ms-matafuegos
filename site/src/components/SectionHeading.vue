<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, default: '' },
    lead: { type: String, default: '' },
    light: { type: Boolean, default: false },
});

defineSlots();
</script>

<template>
    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
            <p v-if="eyebrow" :class="light ? 'eyebrow-light' : 'eyebrow'">
                <span class="h-px w-7" :class="light ? 'bg-brand-400' : 'bg-brand-600'"></span>
                {{ eyebrow }}
            </p>
            <h2 class="mt-4" :class="light ? 'section-title-light' : 'section-title'">
                <slot name="title">{{ title }}</slot>
            </h2>
            <p
                v-if="lead || $slots.lead"
                class="mt-5 text-base leading-7 sm:text-lg"
                :class="light ? 'text-white/60' : 'text-ink-600'"
            >
                <slot name="lead">{{ lead }}</slot>
            </p>
        </div>

        <div v-if="$slots.actions" class="shrink-0">
            <slot name="actions"></slot>
        </div>
    </div>
</template>

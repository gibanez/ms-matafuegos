<script setup>
import logo from '@/assets/ms-matafuegos.png';
import logoLight from '@/assets/ms-matafuegos-light.png';

defineProps({
    light: { type: Boolean, default: false },
    compact: { type: Boolean, default: false },
});
</script>

<template>
    <img
        :src="light ? logoLight : logo"
        alt="MS Matafuegos"
        class="w-auto select-none"
        :class="compact ? 'h-10' : 'h-12 sm:h-14'"
        draggable="false"
    />
</template>

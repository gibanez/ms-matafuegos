<script setup>
import { FireExtinguisher, Flame, ShieldCheck, Truck, Wrench } from '@lucide/vue';

defineProps({
    name: { type: String, required: true },
});

const icons = {
    flame: Flame,
    extinguisher: FireExtinguisher,
    shield: ShieldCheck,
    truck: Truck,
    wrench: Wrench,
};
</script>

<template>
    <component :is="icons[name] || Flame" />
</template>

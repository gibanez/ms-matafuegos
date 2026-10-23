<script setup>
import { reactive, ref } from 'vue';
import {
    AlertCircle,
    CheckCircle2,
    Clock,
    Loader2,
    MapPin,
    MessageCircle,
    Phone,
    Send,
    ShieldCheck,
} from '@lucide/vue';
import PageHero from '@/components/PageHero.vue';
import { createInquiry } from '@/api';
import { business, faqs, whatsappUrl } from '@/data/site';
import { onlyDigits } from '@/utils';

const form = reactive({
    name: '',
    email: '',
    phone: '',
    message: '',
});

const errors = reactive({
    name: '',
    email: '',
    phone: '',
    message: '',
});

const sending = ref(false);
const success = ref(false);
const serverError = ref('');

function resetErrors() {
    errors.name = '';
    errors.email = '';
    errors.phone = '';
    errors.message = '';
    serverError.value = '';
}

function validate() {
    resetErrors();
    let valid = true;

    if (form.name.trim().length < 2) {
        errors.name = 'Ingresá tu nombre.';
        valid = false;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
        errors.email = 'Ingresá un email válido.';
        valid = false;
    }

    const digits = onlyDigits(form.phone);

    if (digits.length < 8) {
        errors.phone = 'Ingresá tu WhatsApp con código de área.';
        valid = false;
    }

    if (form.message.trim().length < 5) {
        errors.message = 'Contanos brevemente qué necesitás.';
        valid = false;
    }

    return valid;
}

async function submit() {
    if (sending.value || !validate()) {
        return;
    }

    sending.value = true;
    serverError.value = '';

    try {
        await createInquiry({
            name: form.name.trim(),
            email: form.email.trim(),
            phone: form.phone.trim(),
            message: form.message.trim(),
        });

        success.value = true;
        form.name = '';
        form.email = '';
        form.phone = '';
        form.message = '';
    } catch (error) {
        serverError.value =
            error.status === 422
                ? 'Revisá los datos ingresados. El WhatsApp y el email tienen que ser válidos.'
                : error.message;
    } finally {
        sending.value = false;
    }
}

function sendAnother() {
    success.value = false;
    resetErrors();
}
</script>

<template>
    <div>
        <PageHero
            eyebrow="Presupuesto sin cargo"
            title="Contanos qué necesitás"
            lead="Completá tus datos y tu WhatsApp. Te respondemos con la mejor opción para tu casa, vehículo, comercio o consorcio."
            breadcrumb="Consultas"
        />

        <section class="py-12 sm:py-16 lg:py-20">
            <div class="container-page grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
                <aside class="relative overflow-hidden rounded-3xl bg-ink-950 p-7 text-white shadow-soft sm:p-9 lg:sticky lg:top-28">
                    <div class="absolute inset-0 bg-grid opacity-60"></div>
                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-brand-600/25 blur-3xl"></div>

                    <div class="relative">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-600 text-white">
                            <ShieldCheck class="h-6 w-6" />
                        </span>
                        <h2 class="mt-6 font-display text-4xl font-black uppercase leading-none">
                            Respuesta rápida y sin compromiso
                        </h2>
                        <p class="mt-4 text-sm leading-7 text-white/60">
                            Si preferís, escribinos directo por WhatsApp. Contanos la cantidad de equipos
                            y el lugar donde los necesitás.
                        </p>

                        <a
                            :href="whatsappUrl()"
                            target="_blank"
                            rel="noopener"
                            class="btn-primary mt-7 w-full"
                        >
                            <MessageCircle class="h-4 w-4" />
                            Hablar por WhatsApp
                        </a>

                        <ul class="mt-8 space-y-4 border-t border-white/10 pt-7 text-sm text-white/65">
                            <li class="flex gap-3">
                                <Phone class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                                <a :href="`tel:${business.phone}`" class="font-semibold text-white hover:text-brand-300">
                                    {{ business.phoneDisplay }}
                                </a>
                            </li>
                            <li class="flex gap-3">
                                <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                                <span>{{ business.address }}, {{ business.city }}</span>
                            </li>
                            <li class="flex gap-3">
                                <Clock class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                                <span>{{ business.hours }}</span>
                            </li>
                        </ul>
                    </div>
                </aside>

                <div class="card p-6 sm:p-9">
                    <div v-if="success" class="flex min-h-[420px] flex-col items-center justify-center text-center">
                        <span class="grid h-16 w-16 place-items-center rounded-full bg-safety-500/10 text-safety-600">
                            <CheckCircle2 class="h-8 w-8" />
                        </span>
                        <h2 class="mt-6 font-display text-4xl font-black uppercase text-ink-950">
                            ¡Consulta enviada!
                        </h2>
                        <p class="mt-3 max-w-md text-sm leading-7 text-ink-500">
                            Gracias por escribirnos. Te vamos a responder a la brevedad con el
                            presupuesto o la información que necesitás.
                        </p>
                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <a :href="whatsappUrl()" target="_blank" rel="noopener" class="btn-primary">
                                <MessageCircle class="h-4 w-4" />
                                Escribir por WhatsApp
                            </a>
                            <button type="button" class="btn-light" @click="sendAnother">
                                Enviar otra consulta
                            </button>
                        </div>
                    </div>

                    <form v-else class="space-y-5" novalidate @submit.prevent="submit">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-700">
                                Formulario de consulta
                            </p>
                            <h2 class="mt-2 font-display text-3xl font-black uppercase text-ink-950 sm:text-4xl">
                                Pedí tu presupuesto
                            </h2>
                            <p class="mt-2 text-sm leading-6 text-ink-500">
                                Los campos marcados con * son obligatorios. El WhatsApp nos permite
                                responderte más rápido.
                            </p>
                        </div>

                        <div
                            v-if="serverError"
                            class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"
                            role="alert"
                        >
                            <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                            <span>{{ serverError }}</span>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="name" class="field-label">Nombre *</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    autocomplete="name"
                                    class="field-input"
                                    :class="{ 'field-error': errors.name }"
                                    placeholder="Tu nombre"
                                    :aria-invalid="Boolean(errors.name)"
                                    aria-describedby="name-error"
                                />
                                <p v-if="errors.name" id="name-error" class="mt-1.5 text-xs font-medium text-red-600">
                                    {{ errors.name }}
                                </p>
                            </div>

                            <div>
                                <label for="phone" class="field-label">WhatsApp *</label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    inputmode="tel"
                                    autocomplete="tel"
                                    class="field-input"
                                    :class="{ 'field-error': errors.phone }"
                                    placeholder="381 597-7614"
                                    :aria-invalid="Boolean(errors.phone)"
                                    aria-describedby="phone-error"
                                />
                                <p v-if="errors.phone" id="phone-error" class="mt-1.5 text-xs font-medium text-red-600">
                                    {{ errors.phone }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="email" class="field-label">Email *</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                class="field-input"
                                :class="{ 'field-error': errors.email }"
                                placeholder="tu@email.com"
                                :aria-invalid="Boolean(errors.email)"
                                aria-describedby="email-error"
                            />
                            <p v-if="errors.email" id="email-error" class="mt-1.5 text-xs font-medium text-red-600">
                                {{ errors.email }}
                            </p>
                        </div>

                        <div>
                            <label for="message" class="field-label">¿Qué necesitás? *</label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="5"
                                class="field-input resize-none"
                                :class="{ 'field-error': errors.message }"
                                placeholder="Ej: necesito recargar 3 matafuegos para un local comercial en el centro."
                                :aria-invalid="Boolean(errors.message)"
                                aria-describedby="message-error"
                            ></textarea>
                            <p v-if="errors.message" id="message-error" class="mt-1.5 text-xs font-medium text-red-600">
                                {{ errors.message }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-4 border-t border-ink-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <p class="max-w-sm text-xs leading-5 text-ink-400">
                                Usamos tus datos únicamente para responder tu consulta.
                            </p>
                            <button type="submit" class="btn-primary shrink-0" :disabled="sending">
                                <Loader2 v-if="sending" class="h-4 w-4 animate-spin" />
                                <Send v-else class="h-4 w-4" />
                                {{ sending ? 'Enviando...' : 'Enviar consulta' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="border-t border-ink-950/5 bg-white py-16 lg:py-20">
            <div class="container-page grid gap-10 lg:grid-cols-[0.75fr_1.25fr] lg:items-start">
                <div>
                    <p class="eyebrow">
                        <span class="h-px w-7 bg-brand-600"></span>
                        Preguntas frecuentes
                    </p>
                    <h2 class="mt-3 font-display text-4xl font-black uppercase leading-none text-ink-950 sm:text-5xl">
                        Antes de escribirnos
                    </h2>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-ink-500">
                        Si tu duda no aparece acá, mandanos un mensaje. Te respondemos sin compromiso.
                    </p>
                </div>

                <div class="divide-y divide-ink-200 rounded-3xl border border-ink-200 bg-paper px-5 sm:px-7">
                    <details v-for="faq in faqs" :key="faq.question" class="group py-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left font-display text-xl font-bold uppercase text-ink-950 marker:hidden">
                            {{ faq.question }}
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full border border-ink-200 text-ink-500 transition group-open:rotate-45 group-open:border-brand-200 group-open:bg-brand-50 group-open:text-brand-700">
                                <span class="text-xl leading-none">+</span>
                            </span>
                        </summary>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-ink-500">
                            {{ faq.answer }}
                        </p>
                    </details>
                </div>
            </div>
        </section>
    </div>
</template>

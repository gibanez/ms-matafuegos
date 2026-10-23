<script setup>
import { reactive, ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Password from 'primevue/password';
import { useToast } from 'primevue/usetoast';
import logoLight from '../assets/ms-matafuegos-light.png';
import { api } from '../api';
import { setSession } from '../store';

const emit = defineEmits(['logged-in']);
const toast = useToast();

const form = reactive({
    email: '',
    password: '',
});
const loading = ref(false);
const error = ref('');

async function submit() {
    error.value = '';
    loading.value = true;

    try {
        const data = await api('/api/auth/login', {
            method: 'POST',
            body: {
                email: form.email,
                password: form.password,
            },
        });

        setSession(data.token, data.admin);
        toast.add({
            severity: 'success',
            summary: 'Bienvenido',
            detail: 'Sesión iniciada correctamente.',
            life: 2500,
        });
        emit('logged-in');
    } catch (exception) {
        error.value = exception.message || 'No se pudo iniciar sesión.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="login-view">
        <div class="login-orb login-orb-one"></div>
        <div class="login-orb login-orb-two"></div>

        <section class="login-card">
            <div class="login-brand">
                <img :src="logoLight" alt="MS Matafuegos" class="login-logo" />
                <div>
                    <small>Panel de gestión</small>
                </div>
            </div>

            <div class="login-heading">
                <span class="eyebrow">Bienvenido de nuevo</span>
                <h1>Ingresá a tu panel</h1>
                <p>Administrá catálogo, clientes, presupuestos y facturas desde un solo lugar.</p>
            </div>

            <form class="login-form" @submit.prevent="submit">
                <div class="form-field">
                    <label for="login-email">Email</label>
                    <InputText
                        id="login-email"
                        v-model="form.email"
                        type="email"
                        autocomplete="username"
                        placeholder="tu@email.com"
                        required
                    />
                </div>

                <div class="form-field">
                    <label for="login-password">Contraseña</label>
                    <Password
                        id="login-password"
                        v-model="form.password"
                        :feedback="false"
                        toggle-mask
                        autocomplete="current-password"
                        placeholder="••••••••"
                        input-class="w-full"
                        class="w-full"
                        required
                    />
                </div>

                <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>

                <Button
                    type="submit"
                    label="Entrar al panel"
                    icon="pi pi-arrow-right"
                    icon-pos="right"
                    class="login-submit"
                    :loading="loading"
                />
            </form>
        </section>
    </div>
</template>

<style scoped>
.login-view {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.login-orb {
    position: absolute;
    border-radius: 999px;
    filter: blur(8px);
    pointer-events: none;
}

.login-orb-one {
    width: 620px;
    height: 620px;
    right: -180px;
    top: -220px;
    background: radial-gradient(circle, rgba(47, 124, 246, 0.24), transparent 68%);
}

.login-orb-two {
    width: 520px;
    height: 520px;
    left: -180px;
    bottom: -220px;
    background: radial-gradient(circle, rgba(91, 155, 255, 0.12), transparent 70%);
}

.login-card {
    width: min(460px, 100%);
    position: relative;
    z-index: 1;
    padding: 2.1rem;
    border: 1px solid var(--cms-line);
    border-radius: 24px;
    background: linear-gradient(180deg, rgba(21, 26, 35, 0.96), rgba(12, 16, 22, 0.98));
    box-shadow: 0 30px 90px rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(18px);
}

.login-brand {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 2.4rem;
}

.login-logo {
    width: auto;
    height: 44px;
}

.login-brand div {
    display: grid;
    gap: 0.1rem;
}

.login-brand strong {
    font-size: 1.05rem;
    letter-spacing: -0.01em;
}

.login-brand small {
    color: var(--cms-muted);
    font-size: 0.78rem;
}

.login-heading {
    margin-bottom: 1.8rem;
}

.login-heading h1 {
    margin: 0.4rem 0 0.55rem;
    font-size: 1.8rem;
    line-height: 1.15;
    letter-spacing: -0.035em;
}

.login-heading p {
    margin: 0;
    color: var(--cms-muted);
    font-size: 0.92rem;
    line-height: 1.6;
}

.login-form {
    display: grid;
    gap: 1.05rem;
}

.login-submit {
    width: 100%;
    margin-top: 0.3rem;
    justify-content: center;
    padding: 0.8rem 1rem;
}
</style>

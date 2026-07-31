<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const empresa = computed(() => page.props.empresa)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const strength = computed(() => {
    const pwd = form.password
    if (!pwd) return { level: 0, label: '', color: '', bg: '', width: '0%' }

    let score = 0
    if (pwd.length >= 8) score++
    if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++
    if (/\d/.test(pwd)) score++
    if (/[^a-zA-Z0-9]/.test(pwd)) score++

    if (score <= 1) return { level: 1, label: 'Baja', color: 'text-red-600', bg: 'bg-red-500', width: '25%' }
    if (score <= 3) return { level: 2, label: 'Media', color: 'text-yellow-600', bg: 'bg-yellow-400', width: '60%' }
    return { level: 3, label: 'Segura', color: 'text-green-600', bg: 'bg-green-500', width: '100%' }
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Registrarse" />

    <div class="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
        <div class="fixed top-0 left-0 right-0 z-50 h-1" :style="{ backgroundColor: 'var(--color-secondary)' }"></div>
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-white font-bold text-2xl" :style="{ backgroundColor: 'var(--color-primary)', boxShadow: '0 10px 15px -3px color-mix(in srgb, var(--color-primary) 20%, transparent), 0 4px 6px -4px color-mix(in srgb, var(--color-primary) 20%, transparent)' }">
                    {{ empresa?.siglas?.charAt(0) || 'S' }}
                </div>
                <h1 class="text-2xl font-bold text-[var(--color-text)]">Crear Cuenta</h1>
                <p class="text-sm text-[var(--color-text-muted)] mt-1">{{ empresa?.nombre || 'SIGESGA' }}</p>
            </div>

            <div class="neumorphic-card p-8">
                <form @submit.prevent="submit">
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Nombre completo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-[var(--color-text-placeholder)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input v-model="form.name" type="text" placeholder="Tu nombre" required autofocus autocomplete="name"
                                class="w-full bg-[var(--color-bg)] text-[var(--color-text)] placeholder-[var(--color-text-placeholder)] rounded-2xl py-3 pl-12 pr-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
                        </div>
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Correo electrónico</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-[var(--color-text-placeholder)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input v-model="form.email" type="email" placeholder="tu@correo.com" required autocomplete="username"
                                class="w-full bg-[var(--color-bg)] text-[var(--color-text)] placeholder-[var(--color-text-placeholder)] rounded-2xl py-3 pl-12 pr-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-[var(--color-text-placeholder)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input v-model="form.password" type="password" placeholder="••••••••" required autocomplete="new-password"
                                class="w-full bg-[var(--color-bg)] text-[var(--color-text)] placeholder-[var(--color-text-placeholder)] rounded-2xl py-3 pl-12 pr-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
                        </div>
                        <div v-if="form.password" class="mt-3">
                            <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden">
                                <div :class="strength.bg" class="h-full rounded-full transition-all duration-300" :style="{ width: strength.width }"></div>
                            </div>
                            <p :class="strength.color" class="mt-1 text-xs font-medium">{{ strength.label }}</p>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Confirmar contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-[var(--color-text-placeholder)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input v-model="form.password_confirmation" type="password" placeholder="••••••••" required autocomplete="new-password"
                                class="w-full bg-[var(--color-bg)] text-[var(--color-text)] placeholder-[var(--color-text-placeholder)] rounded-2xl py-3 pl-12 pr-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full py-3 px-6 text-white font-medium rounded-2xl transition-all duration-200 disabled:opacity-50" :style="{ backgroundColor: 'var(--color-primary)', boxShadow: '0 10px 15px -3px color-mix(in srgb, var(--color-primary) 20%, transparent), 0 4px 6px -4px color-mix(in srgb, var(--color-primary) 20%, transparent)' }">
                        <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2 align-middle"></span>
                        Crear Cuenta
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-sm text-[var(--color-text-muted)]">
                ¿Ya tienes cuenta?
                <Link :href="route('login')" class="text-[var(--color-primary)] hover:text-[var(--color-primary)] font-medium">Inicia sesión</Link>
            </p>
        </div>
    </div>
</template>

<style scoped>
.neumorphic-card {
    background: var(--color-surface);
    border-radius: 24px;
    box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light);
}
</style>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useTheme } from '@/Composables/useTheme'

useTheme()

const showPassword = ref(false)

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
})

const page = usePage()
const empresa = computed(() => page.props.empresa)

const form = useForm({
    email: '',
    password: '',
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <!-- Página de inicio de sesión -->
    <Head title="Iniciar Sesión" />

    <div class="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
        <div class="fixed top-0 left-0 right-0 z-50 h-1" :style="{ backgroundColor: 'var(--color-secondary)' }"></div>
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div v-if="empresa?.logo" class="w-20 h-20 mx-auto mb-4 rounded-2xl overflow-hidden shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]">
                    <img :src="'/storage/' + empresa.logo" class="w-full h-full object-contain" alt="Logo" />
                </div>
                <div v-else class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg" :style="{ backgroundColor: 'var(--color-primary)' }">
                    {{ empresa?.siglas?.charAt(0) || 'S' }}
                </div>
                <h1 class="text-2xl font-bold text-[var(--color-text)]">Iniciar Sesión</h1>
                <p class="text-sm text-[var(--color-text-muted)] mt-1">{{ empresa?.nombre || 'SIGESGA' }}</p>
            </div>

            <div v-if="status" class="mb-4 p-3 rounded-2xl bg-green-100 text-green-700 text-sm text-center shadow-[2px_2px_4px_#b0d0b6,-2px_-2px_4px_#ffffff]">
                {{ status }}
            </div>

            <div class="neumorphic-card p-8">
                <form @submit.prevent="submit">
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Correo electrónico</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-[var(--color-text-placeholder)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="tu@correo.com"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full bg-[var(--color-bg)] text-[var(--color-text)] placeholder-[var(--color-text-placeholder)] rounded-2xl py-3 pl-12 pr-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition-all duration-200"
                            />
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
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                                class="w-full bg-[var(--color-bg)] text-[var(--color-text)] placeholder-[var(--color-text-placeholder)] rounded-2xl py-3 pl-12 pr-12 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition-all duration-200"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-[var(--color-text-placeholder)] hover:text-[var(--color-text-muted)] transition-colors"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-end mb-6">
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-[var(--color-primary)] hover:text-[var(--color-primary)] font-medium">
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-6 text-white font-medium rounded-2xl transition-all duration-200 disabled:opacity-50" :style="{ backgroundColor: 'var(--color-primary)', boxShadow: '0 10px 15px -3px color-mix(in srgb, var(--color-primary) 20%, transparent), 0 4px 6px -4px color-mix(in srgb, var(--color-primary) 20%, transparent)' }"
                    >
                        <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2 align-middle"></span>
                        Iniciar Sesión
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-sm text-[var(--color-text-muted)]">
                ¿No tienes cuenta?
                <Link :href="route('register')" class="text-[var(--color-primary)] hover:text-[var(--color-primary)] font-medium">Regístrate</Link>
            </p>
            <p v-if="form.errors.email && (form.errors.email.includes('ha sido bloqueada') || form.errors.email.includes('código de desbloqueo'))" class="text-center mt-3 text-sm text-[var(--color-text-muted)]">
                <Link :href="route('unlock')" class="text-[var(--color-primary)] hover:underline font-medium">Desbloquear cuenta</Link>
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

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps({ status: String })

const page = usePage()
const empresa = computed(() => page.props.empresa)

const form = useForm({ email: '' })

const submit = () => { form.post(route('password.email')) }
</script>

<template>
    <!-- Página de recuperación de contraseña -->
    <Head title="Recuperar Contraseña" />
    <div class="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
        <div class="fixed top-0 left-0 right-0 z-50 h-1" :style="{ backgroundColor: 'var(--color-secondary)' }"></div>
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-white font-bold text-2xl" :style="{ backgroundColor: 'var(--color-primary)', boxShadow: '0 10px 15px -3px color-mix(in srgb, var(--color-primary) 20%, transparent), 0 4px 6px -4px color-mix(in srgb, var(--color-primary) 20%, transparent)' }">
                    {{ empresa?.siglas?.charAt(0) || 'S' }}
                </div>
                <h1 class="text-2xl font-bold text-[var(--color-text)]">Recuperar Contraseña</h1>
                <p class="text-sm text-[var(--color-text-muted)] mt-1">Te enviaremos un enlace para restablecerla</p>
            </div>

            <div v-if="status" class="mb-4 p-3 rounded-2xl bg-green-100 text-green-700 text-sm text-center shadow-[2px_2px_4px_#b0d0b6,-2px_-2px_4px_#ffffff]">{{ status }}</div>

            <div class="neumorphic-card p-8">
                <form @submit.prevent="submit">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Correo electrónico</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-[var(--color-text-placeholder)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input v-model="form.email" type="email" placeholder="tu@correo.com" required autofocus
                                class="w-full bg-[var(--color-bg)] text-[var(--color-text)] placeholder-[var(--color-text-placeholder)] rounded-2xl py-3 pl-12 pr-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-500">{{ form.errors.email }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full py-3 px-6 text-white font-medium rounded-2xl transition-all duration-200 disabled:opacity-50" :style="{ backgroundColor: 'var(--color-primary)', boxShadow: '0 10px 15px -3px color-mix(in srgb, var(--color-primary) 20%, transparent), 0 4px 6px -4px color-mix(in srgb, var(--color-primary) 20%, transparent)' }">
                        <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
                        Enviar enlace
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.neumorphic-card {
    background: var(--color-surface); border-radius: 24px;
    box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light);
}
</style>

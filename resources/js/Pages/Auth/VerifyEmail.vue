<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const empresa = computed(() => page.props.empresa)

const form = useForm({})

const submit = () => { form.post(route('verification.send')) }
</script>

<template>
    <!-- Página de verificación de correo electrónico -->
    <Head title="Verificar Correo" />
    <div class="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
        <div class="fixed top-0 left-0 right-0 z-50 h-1" :style="{ backgroundColor: 'var(--color-secondary)' }"></div>
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-white font-bold text-2xl" :style="{ backgroundColor: 'var(--color-primary)', boxShadow: '0 10px 15px -3px color-mix(in srgb, var(--color-primary) 20%, transparent), 0 4px 6px -4px color-mix(in srgb, var(--color-primary) 20%, transparent)' }">
                    {{ empresa?.siglas?.charAt(0) || 'S' }}
                </div>
                <h1 class="text-2xl font-bold text-[var(--color-text)]">Verifica tu correo</h1>
                <p class="text-sm text-[var(--color-text-muted)] mt-1">Te enviamos un enlace de verificación a tu correo</p>
            </div>

            <div class="neumorphic-card p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[var(--color-bg)] flex items-center justify-center shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff]">
                    <svg class="w-8 h-8 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </div>
                <p class="text-[var(--color-text)] text-sm mb-6">Antes de continuar, revisa tu correo para el enlace de verificación. Si no lo recibiste, solicita uno nuevo.</p>
                <form @submit.prevent="submit">
                    <button type="submit" :disabled="form.processing"
                        class="w-full py-3 px-6 text-white font-medium rounded-2xl transition-all duration-200 disabled:opacity-50" :style="{ backgroundColor: 'var(--color-primary)', boxShadow: '0 10px 15px -3px color-mix(in srgb, var(--color-primary) 20%, transparent), 0 4px 6px -4px color-mix(in srgb, var(--color-primary) 20%, transparent)' }">
                        <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
                        Reenviar verificación
                    </button>
                </form>
                <Link :href="route('logout')" method="post" as="button" class="mt-4 text-sm text-[var(--color-text-muted)] hover:text-[var(--color-primary)]">
                    Cerrar sesión
                </Link>
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

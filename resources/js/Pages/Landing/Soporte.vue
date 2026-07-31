<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const empresa = computed(() => usePage().props.empresa || {})
</script>

<template>
    <Head title="Soporte - SIGESGA" />

    <div class="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
        <div class="max-w-xl w-full space-y-8">
            <div class="p-8 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Soporte Técnico</h1>
                    <p class="text-gray-500">Estamos aquí para ayudarte. Contáctanos por cualquier medio.</p>
                </div>

                <div class="space-y-5">
                    <div class="flex items-center gap-4 p-5 rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]">
                        <div class="w-12 h-12 rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] flex items-center justify-center shrink-0"
                             :style="{ color: 'var(--color-primary)' }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-medium">Teléfono</p>
                            <p class="text-[var(--color-text)] font-medium">{{ empresa.telefono_contacto || '(555) 123-4567' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-5 rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]">
                        <div class="w-12 h-12 rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] flex items-center justify-center shrink-0"
                             :style="{ color: 'var(--color-primary)' }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-medium">Email</p>
                            <p class="text-[var(--color-text)] font-medium">{{ empresa.email_contacto || 'soporte@sigesga.com' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-5 rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]">
                        <div class="w-12 h-12 rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] flex items-center justify-center shrink-0"
                             :style="{ color: 'var(--color-primary)' }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-medium">Ubicación</p>
                            <p class="text-[var(--color-text)] font-medium">{{ empresa.direccion || 'Oficina Central' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="/" class="text-sm text-gray-500 hover:text-[var(--color-text)] transition-colors">
                        ← Volver al inicio
                    </a>
                </div>
            </div>

            <div class="p-6 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] text-center">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Horario de Atención</h3>
                <p class="text-gray-500">Lunes a Domingo - 24 horas</p>
                <p class="text-sm text-gray-400 mt-1">Servicio de emergencias disponible todo el año</p>
            </div>
        </div>
    </div>
</template>
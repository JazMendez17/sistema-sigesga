<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const form = ref({
    ubicacion: '',
    tipo_servicio: '',
    descripcion: '',
})

const submitForm = () => {
    router.post(route('solicitar.store'), form.value)
}
</script>

<template>
    <!-- Formulario público de solicitud de servicio -->
    <Head title="Solicitar Servicio - SIGESGA" />

    <div class="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
        <div class="max-w-lg w-full">
            <div class="p-8 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Solicitar Servicio</h1>
                    <p class="text-gray-500">Solicita una grúa o asistencia vial de forma rápida.</p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">Ubicación</label>
                        <input v-model="form.ubicacion" type="text" required
                               class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none"
                               placeholder="Calle, colonia, referencia" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">Tipo de Servicio</label>
                        <select v-model="form.tipo_servicio" required
                                class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] focus:outline-none">
                            <option value="">Selecciona un servicio</option>
                            <option>Grúa Ligera</option>
                            <option>Grúa Pesada</option>
                            <option>Asistencia Vial</option>
                            <option>Cambio de Llantas</option>
                            <option>Carga de Batería</option>
                            <option>Cerrajería Automotriz</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">Descripción</label>
                        <textarea v-model="form.descripcion" rows="4"
                                  class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none resize-none"
                                  placeholder="Describe tu situación..."></textarea>
                    </div>
                    <button type="submit"
                            class="w-full py-3.5 rounded-2xl text-sm font-semibold text-white transition-all duration-200 shadow-[6px_6px_12px_#d0d5da,-6px_-6px_12px_#ffffff] hover:shadow-[8px_8px_16px_#c9ced3,-8px_-8px_16px_#ffffff]"
                            :style="{ backgroundColor: 'var(--color-primary)' }">
                        Enviar Solicitud
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="/" class="text-sm text-gray-500 hover:text-[var(--color-text)] transition-colors">
                        ← Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
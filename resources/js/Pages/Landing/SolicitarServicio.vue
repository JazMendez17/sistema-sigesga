<script setup>
import { computed } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme'

useTheme()

const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success || '')

const form = useForm({
    nombre: '',
    telefono: '',
    email: '',
    ubicacion: '',
    tipo_servicio: '',
    descripcion: '',
})

const submitForm = () => {
    form.post(route('solicitar.store'), {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <!-- Formulario público de solicitud de servicio -->
    <Head title="Solicitar Servicio - SIGESGA" />

    <div class="min-h-screen bg-[var(--color-bg,#E8EDF2)] flex items-center justify-center p-4">
        <div class="max-w-lg w-full">
            <div class="p-8 rounded-3xl bg-[var(--color-bg,#E8EDF2)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Solicitar Servicio</h1>
                    <p class="text-gray-500">Solicita una grúa o asistencia vial de forma rápida.</p>
                </div>

                <div v-if="flashSuccess" class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm font-medium text-emerald-800">
                    {{ flashSuccess }}
                </div>

                <form @submit.prevent="submitForm" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">Nombre</label>
                            <input v-model="form.nombre" type="text"
                                   class="w-full px-4 py-3 rounded-2xl bg-[var(--color-bg,#E8EDF2)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none"
                                   placeholder="Tu nombre" />
                            <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">Teléfono</label>
                            <input v-model="form.telefono" type="tel"
                                   class="w-full px-4 py-3 rounded-2xl bg-[var(--color-bg,#E8EDF2)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none"
                                   placeholder="55 1234 5678" />
                            <p v-if="form.errors.telefono" class="mt-1 text-xs text-red-500">{{ form.errors.telefono }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Correo electrónico (opcional)</label>
                        <input v-model="form.email" type="email"
                               class="w-full px-4 py-3 rounded-2xl bg-[var(--color-bg,#E8EDF2)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none"
                               placeholder="tu@correo.com" />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Ubicación *</label>
                        <input v-model="form.ubicacion" type="text" required
                               class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg,#E8EDF2)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none"
                               placeholder="Calle, colonia, referencia" />
                        <p v-if="form.errors.ubicacion" class="mt-1 text-xs text-red-500">{{ form.errors.ubicacion }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Tipo de Servicio *</label>
                        <select v-model="form.tipo_servicio" required
                                class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg,#E8EDF2)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] focus:outline-none">
                            <option value="">Selecciona un servicio</option>
                            <option>Grúa Ligera</option>
                            <option>Grúa Pesada</option>
                            <option>Asistencia Vial</option>
                            <option>Cambio de Llantas</option>
                            <option>Carga de Batería</option>
                            <option>Cerrajería Automotriz</option>
                        </select>
                        <p v-if="form.errors.tipo_servicio" class="mt-1 text-xs text-red-500">{{ form.errors.tipo_servicio }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Descripción</label>
                        <textarea v-model="form.descripcion" rows="4"
                                  class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg,#E8EDF2)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none resize-none"
                                  placeholder="Describe tu situación..."></textarea>
                        <p v-if="form.errors.descripcion" class="mt-1 text-xs text-red-500">{{ form.errors.descripcion }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing"
                            class="w-full py-3.5 rounded-2xl text-sm font-semibold text-white transition-all duration-200 shadow-[6px_6px_12px_#d0d5da,-6px_-6px_12px_#ffffff] hover:shadow-[8px_8px_16px_#c9ced3,-8px_-8px_16px_#ffffff] disabled:opacity-50"
                            :style="{ backgroundColor: 'var(--color-primary, #4F46E5)' }">
                        {{ form.processing ? 'Enviando...' : 'Enviar Solicitud' }}
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

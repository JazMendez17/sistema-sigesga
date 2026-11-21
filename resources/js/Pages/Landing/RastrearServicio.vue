<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { useTheme } from '@/Composables/useTheme'

useTheme()

const props = defineProps({
    folio: { type: String, default: '' },
    resultado: { type: Object, default: null },
    buscado: { type: Boolean, default: false },
})

const codigo = ref(props.folio || '')
const buscando = ref(false)

watch(() => props.folio, (val) => {
    if (val) codigo.value = val
})

const buscar = () => {
    const limpio = codigo.value.trim()
    if (!limpio) return
    buscando.value = true
    router.get('/rastrear', { folio: limpio }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => { buscando.value = false },
    })
}
</script>

<template>
    <!-- Página de rastreo de servicio por folio -->
    <Head title="Rastrear Servicio - SIGESGA" />

    <div class="min-h-screen bg-[var(--color-bg,#E8EDF2)] flex items-center justify-center p-4">
        <div class="max-w-lg w-full">
            <div class="p-8 rounded-3xl bg-[var(--color-bg,#E8EDF2)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Rastrear Servicio</h1>
                    <p class="text-gray-500">Ingresa tu folio de servicio o cotización (ej. SVC-00001 o COT-00001).</p>
                </div>

                <form @submit.prevent="buscar" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">Código de Seguimiento</label>
                        <input v-model="codigo" type="text" required
                               class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg,#E8EDF2)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-gray-700 placeholder-gray-400 focus:outline-none"
                               placeholder="Ej: SVC-00001" />
                    </div>
                    <button type="submit" :disabled="buscando"
                            class="w-full py-3.5 rounded-2xl text-sm font-semibold text-white transition-all duration-200 shadow-[6px_6px_12px_#d0d5da,-6px_-6px_12px_#ffffff] hover:shadow-[8px_8px_16px_#c9ced3,-8px_-8px_16px_#ffffff] disabled:opacity-50"
                            :style="{ backgroundColor: 'var(--color-primary, #4F46E5)' }">
                        {{ buscando ? 'Buscando...' : 'Rastrear' }}
                    </button>
                </form>

                <div v-if="buscado && resultado" class="mt-6 p-6 rounded-2xl bg-white/80 shadow-inner space-y-3 text-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                        <span class="font-bold text-gray-800">{{ resultado.folio }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold text-white" :style="{ backgroundColor: 'var(--color-primary, #4F46E5)' }">
                            {{ $etiqueta(resultado.estatus) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tipo de servicio:</span>
                        <span class="font-medium text-gray-800">{{ resultado.tipo_servicio }}</span>
                    </div>
                    <div v-if="resultado.fecha" class="flex justify-between">
                        <span class="text-gray-500">Fecha de registro:</span>
                        <span class="font-medium text-gray-800">{{ resultado.fecha }}</span>
                    </div>
                    <div v-if="resultado.bitacora" class="border-t border-gray-200 pt-3 space-y-1.5 text-xs text-gray-600">
                        <p class="font-semibold text-gray-700 uppercase">Bitácora de avance</p>
                        <p v-if="resultado.bitacora.asignado">Asignado: <strong>{{ resultado.bitacora.asignado }}</strong></p>
                        <p v-if="resultado.bitacora.inicio">En camino: <strong>{{ resultado.bitacora.inicio }}</strong></p>
                        <p v-if="resultado.bitacora.llegada_origen">En origen: <strong>{{ resultado.bitacora.llegada_origen }}</strong></p>
                        <p v-if="resultado.bitacora.en_destino">En destino: <strong>{{ resultado.bitacora.en_destino }}</strong></p>
                        <p v-if="resultado.bitacora.finalizado">Finalizado: <strong>{{ resultado.bitacora.finalizado }}</strong></p>
                    </div>
                </div>

                <div v-else-if="buscado && !resultado" class="mt-6 p-6 rounded-2xl bg-[var(--color-bg,#E8EDF2)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff]">
                    <p class="text-center text-gray-500">
                        No se encontró información para el código proporcionado.
                        <br>
                        <span class="text-sm">Por favor verifica el código e intenta de nuevo.</span>
                    </p>
                </div>

                <div class="mt-6 text-center">
                    <a href="/" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        ← Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

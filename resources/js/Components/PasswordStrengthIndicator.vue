<script setup>
import { computed } from 'vue'

const props = defineProps({
    password: { type: String, default: '' },
    resultados: { type: Object, default: () => ({}) },
    level: { type: Object, default: () => ({ level: 0, label: '', color: '#9CA3AF', width: '0%' }) },
    errores: { type: Array, default: () => [] },
    submitted: { type: Boolean, default: false },
})

const mensaje = computed(() => {
    if (props.level.level >= 2) return null
    if (props.level.level === 0) return 'La contraseña es demasiado débil. Solo se aceptan contraseñas de nivel Alto o Seguro.'
    return 'La contraseña es débil. Solo se aceptan contraseñas de nivel Alto o Seguro.'
})
</script>

<template>
    <div v-if="password" class="space-y-3">
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium" :style="{ color: level.color }">{{ level.label }}</span>
                <span class="text-xs text-gray-400">5/5 requisitos</span>
            </div>
            <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden shadow-[inset_2px_2px_4px_#c0c4c8]">
                <div
                    class="h-full rounded-full transition-all duration-500 ease-out"
                    :style="{ width: level.width, backgroundColor: level.color }"
                />
            </div>
            <p v-if="mensaje" class="text-xs text-red-500 font-medium mt-1">{{ mensaje }}</p>
        </div>

        <div class="space-y-1.5">
            <div
                v-for="(req, key) in resultados"
                :key="key"
                class="flex items-center gap-2 text-xs transition-colors duration-200"
                :class="req.met ? 'text-green-600' : (submitted && !req.met ? 'text-red-500' : 'text-gray-400')"
            >
                <svg v-if="req.met" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ req.label }}</span>
            </div>
        </div>

        <div v-if="submitted && errores.length" class="rounded-xl bg-red-50 border border-red-200 p-3">
            <p class="text-xs font-medium text-red-700 mb-1">Requisitos faltantes:</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li v-for="(err, i) in errores" :key="i" class="text-xs text-red-600">{{ err }}</li>
            </ul>
        </div>
    </div>
</template>

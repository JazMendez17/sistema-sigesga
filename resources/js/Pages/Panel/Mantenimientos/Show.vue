<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  mantenimiento: { type: Object, required: true },
})

function formato(val) {
  return val || '—'
}
</script>

<template>
  <!-- Detalle del mantenimiento -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Mantenimiento #{{ mantenimiento.id }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle del mantenimiento</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.mantenimientos.index'))">Volver</NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.mantenimientos.edit', mantenimiento.id))">Editar</NeumorphicButton>
        </div>
      </div>

      <!-- Datos del mantenimiento -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos del Mantenimiento</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Unidad</p><p class="text-sm font-medium text-gray-800">{{ mantenimiento.unidad?.nombre || mantenimiento.unidad?.placas || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Tipo</p><p class="text-sm font-medium text-gray-800">{{ formato(mantenimiento.tipo) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Fecha</p><p class="text-sm font-medium text-gray-800">{{ formato(mantenimiento.fecha) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Kilometraje</p><p class="text-sm font-medium text-gray-800">{{ mantenimiento.kilometraje ? Number(mantenimiento.kilometraje).toLocaleString() + ' km' : '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Costo</p><p class="text-sm font-medium text-gray-800">{{ mantenimiento.costo ? '$' + parseFloat(mantenimiento.costo).toFixed(2) : '—' }}</p></div>
          <div></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Próximo Mantenimiento (Fecha)</p><p class="text-sm font-medium text-gray-800">{{ formato(mantenimiento.proximo_mantenimiento_fecha) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Próximo Mantenimiento (Km)</p><p class="text-sm font-medium text-gray-800">{{ mantenimiento.proximo_mantenimiento_km ? Number(mantenimiento.proximo_mantenimiento_km).toLocaleString() + ' km' : '—' }}</p></div>
          <div></div>
        </div>
      </div>

      <!-- Observaciones -->
      <div v-if="mantenimiento.observaciones" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Observaciones</h3>
        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ mantenimiento.observaciones }}</p>
      </div>
    </div>
  </AppLayout>
</template>

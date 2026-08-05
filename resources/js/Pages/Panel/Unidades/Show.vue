<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  unidad: { type: Object, required: true },
})
</script>

<template>
  <!-- Detalle de unidad vehicular -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ unidad.numero_economico }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalles de la unidad</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.unidades.index'))">
            Volver
          </NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.unidades.edit', unidad.id))">
            Editar
          </NeumorphicButton>
        </div>
      </div>
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos Generales</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Marca</p><p class="text-sm font-medium text-gray-800">{{ unidad.marca || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Modelo</p><p class="text-sm font-medium text-gray-800">{{ unidad.modelo || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Tipo</p><p class="text-sm font-medium text-gray-800">{{ unidad.tipo || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Placas</p><p class="text-sm font-medium text-gray-800">{{ unidad.placas || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Serie</p><p class="text-sm font-medium text-gray-800">{{ unidad.serie || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Oficina</p><p class="text-sm font-medium text-gray-800">{{ unidad.oficina?.nombre || '—' }}</p></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

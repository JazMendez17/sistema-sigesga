<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  cotizacion: Object,
})
</script>

<template>
  <!-- Detalle de cotización -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ cotizacion?.folio || 'COT-00124' }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle de cotización</p>
        </div>
        <NeumorphicButton @click="router.visit(route('panel.cotizaciones.index'))">Volver</NeumorphicButton>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="neumorphic-card p-6 lg:col-span-2 space-y-5">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Cliente</p>
              <p class="text-gray-800 font-medium">{{ cotizacion?.cliente || 'Juan Pérez' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Fecha</p>
              <p class="text-gray-800 font-medium">{{ cotizacion?.fecha || '23 Jul 2026' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Tipo de Servicio</p>
              <p class="text-gray-800 font-medium">{{ cotizacion?.tipo_servicio || 'Transporte Local' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Estatus</p>
              <Badge :variant="cotizacion?.estatus || 'warning'">{{ cotizacion?.estatus || 'Pendiente' }}</Badge>
            </div>
            <div class="col-span-2">
              <p class="text-xs text-gray-500 uppercase tracking-wider">Origen</p>
              <p class="text-gray-800 font-medium">{{ cotizacion?.origen || 'Centro, Ciudad de México' }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-xs text-gray-500 uppercase tracking-wider">Destino</p>
              <p class="text-gray-800 font-medium">{{ cotizacion?.destino || 'Norte, Ciudad de México' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Distancia</p>
              <p class="text-gray-800 font-medium">{{ cotizacion?.distancia || '15 km' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Estimado</p>
              <p class="text-xl font-bold text-[var(--color-primary)]">${{ cotizacion?.total || '1,200' }}</p>
            </div>
          </div>
        </div>

        <div class="neumorphic-card p-6 space-y-4">
          <h3 class="font-semibold text-gray-800">Historial</h3>
          <div class="space-y-3">
            <div v-if="cotizacion?.historial" v-for="(item, i) in cotizacion.historial" :key="i" class="text-sm">
              <p class="text-gray-500">{{ item }}</p>
            </div>
            <div v-else class="text-sm">
              <p class="text-gray-500">23 Jul - Creada por Admin</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card {
  background: var(--color-surface);
  border-radius: 24px;
  box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light);
}
</style>

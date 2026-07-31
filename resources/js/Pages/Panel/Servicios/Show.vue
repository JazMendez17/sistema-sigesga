<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  servicio: Object,
})
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ servicio?.folio || 'SVC-00089' }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle del servicio</p>
        </div>
        <NeumorphicButton @click="router.visit(route('panel.servicios.index'))">Volver</NeumorphicButton>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="neumorphic-card p-6 lg:col-span-2 space-y-5">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Cliente</p>
              <p class="text-gray-800 font-medium">{{ servicio?.cliente || 'María García' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Fecha</p>
              <p class="text-gray-800 font-medium">{{ servicio?.fecha || '23 Jul 2026' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Operador</p>
              <p class="text-gray-800 font-medium">{{ servicio?.operador || 'Roberto Méndez' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Unidad</p>
              <p class="text-gray-800 font-medium">{{ servicio?.unidad || 'Grúa 01' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Tipo de Servicio</p>
              <p class="text-gray-800 font-medium">{{ servicio?.tipo_servicio || 'Transporte Local' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Estatus</p>
              <Badge :variant="servicio?.estatus || 'info'">{{ servicio?.estatus || 'Asignado' }}</Badge>
            </div>
            <div class="col-span-2">
              <p class="text-xs text-gray-500 uppercase tracking-wider">Origen</p>
              <p class="text-gray-800 font-medium">{{ servicio?.origen || 'Av. Reforma, Ciudad de México' }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-xs text-gray-500 uppercase tracking-wider">Destino</p>
              <p class="text-gray-800 font-medium">{{ servicio?.destino || 'Zona Industrial, Ciudad de México' }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-xs text-gray-500 uppercase tracking-wider">Observaciones</p>
              <p class="text-gray-600">{{ servicio?.observaciones || 'Cliente solicita grúa de plataforma para vehículo sedán.' }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="neumorphic-card p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Bitácora de Tiempos</h3>
            <div class="space-y-2 text-sm">
              <p class="text-gray-500">Salida: <span class="text-gray-800">{{ servicio?.bitacora?.salida || '08:30' }}</span></p>
              <p class="text-gray-500">Llegada a cliente: <span class="text-gray-800">{{ servicio?.bitacora?.llegada || '09:15' }}</span></p>
              <p class="text-gray-500">Término servicio: <span class="text-gray-800">{{ servicio?.bitacora?.termino || '10:00' }}</span></p>
              <p class="text-gray-500">Regreso base: <span class="text-gray-800">{{ servicio?.bitacora?.regreso || '10:45' }}</span></p>
            </div>
          </div>

          <div class="neumorphic-card p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Kilometraje</h3>
            <div class="space-y-2 text-sm">
              <p class="text-gray-500">Salida: <span class="text-gray-800">{{ servicio?.kilometraje?.salida || '12,450 km' }}</span></p>
              <p class="text-gray-500">Regreso: <span class="text-gray-800">{{ servicio?.kilometraje?.regreso || '12,485 km' }}</span></p>
              <p class="text-gray-500">Cobrados: <span class="text-gray-800">{{ servicio?.kilometraje?.cobrados || '35 km' }}</span></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card {
  background: #EEF2F7;
  border-radius: 24px;
  box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff;
}
</style>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({ factura: Object })

function formato(val) { return val || '—' }
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ formato(factura?.folio_factura) }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle de factura</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.facturacion.index'))">Volver</NeumorphicButton>
          <NeumorphicButton v-if="factura?.correo_envio_factura" @click="router.post(route('panel.facturacion.enviar', { id: factura.id }))">Reenviar PDF</NeumorphicButton>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Datos generales -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Factura</h3>
          <div class="space-y-3">
            <div><p class="text-xs text-gray-500 uppercase">Folio</p><p class="text-sm font-medium">{{ formato(factura?.folio_factura) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Fecha</p><p class="text-sm font-medium">{{ formato(factura?.fecha) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Estatus</p><Badge :variant="factura?.estatus === 'vigente' ? 'success' : 'danger'">{{ formato(factura?.estatus) }}</Badge></div>
            <div><p class="text-xs text-gray-500 uppercase">Email envío</p><p class="text-sm font-medium">{{ formato(factura?.correo_envio_factura) }}</p></div>
          </div>
        </div>

        <!-- Cliente -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Cliente</h3>
          <div class="space-y-3">
            <div><p class="text-xs text-gray-500 uppercase">Nombre</p><p class="text-sm font-medium">{{ formato(factura?.cliente?.nombre) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Email</p><p class="text-sm font-medium">{{ formato(factura?.cliente?.email) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Teléfono</p><p class="text-sm font-medium">{{ formato(factura?.cliente?.telefono) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Aseguradora</p><p class="text-sm font-medium">{{ formato(factura?.cliente?.aseguradora) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Póliza</p><p class="text-sm font-medium">{{ formato(factura?.cliente?.poliza) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Cobertura</p><p class="text-sm font-medium">{{ formato(factura?.cliente?.cobertura) }}</p></div>
          </div>
        </div>

        <!-- Servicio -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Servicio</h3>
          <div class="space-y-3">
            <div><p class="text-xs text-gray-500 uppercase">Folio</p><p class="text-sm font-medium">{{ formato(factura?.servicio?.folio) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Tipo</p><p class="text-sm font-medium">{{ formato(factura?.servicio?.tipo) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Origen</p><p class="text-sm font-medium">{{ formato(factura?.servicio?.origen) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Destino</p><p class="text-sm font-medium">{{ formato(factura?.servicio?.destino) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Distancia</p><p class="text-sm font-medium">{{ factura?.servicio?.distancia }} km</p></div>
            <div><p class="text-xs text-gray-500 uppercase">KM Incluidos</p><p class="text-sm font-medium">{{ factura?.servicio?.km_incluidos }} km</p></div>
          </div>
        </div>

        <!-- Costos -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] lg:col-span-3">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Desglose de Costos</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex justify-between items-center p-3 rounded-xl bg-white"><span class="text-gray-500">Banderazo:</span><span class="font-medium">${{ factura?.servicio?.banderazo?.toFixed(2) || '0.00' }}</span></div>
            <div class="flex justify-between items-center p-3 rounded-xl bg-white"><span class="text-gray-500">Costo KM:</span><span class="font-medium">${{ factura?.servicio?.costo_km?.toFixed(2) || '0.00' }}/km</span></div>
            <div class="flex justify-between items-center p-3 rounded-xl bg-white"><span class="text-gray-500">Subtotal:</span><span class="font-medium">${{ factura?.subtotal?.toFixed(2) || '0.00' }}</span></div>
            <div class="flex justify-between items-center p-3 rounded-xl bg-white"><span class="text-gray-500">IVA (16%):</span><span class="font-medium">${{ factura?.iva?.toFixed(2) || '0.00' }}</span></div>
            <div class="flex justify-between items-center p-3 rounded-xl bg-[var(--color-primary)] text-white col-span-2 md:col-span-1"><span class="font-semibold">TOTAL:</span><span class="text-xl font-bold">${{ factura?.total?.toFixed(2) || '0.00' }}</span></div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

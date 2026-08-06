<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
  convenio: { type: Object, required: true },
})

function formato(val) {
  return val || '—'
}

function siNo(val) {
  return val ? 'Sí' : 'No'
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ convenio.nombre }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle del convenio</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.convenios.index'))">Volver</NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.convenios.edit', convenio.id))">Editar</NeumorphicButton>
        </div>
      </div>

      <!-- Datos Generales -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos Generales</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase">Nombre</p><p class="text-sm font-medium text-gray-800">{{ formato(convenio.nombre) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Código</p><p class="text-sm font-medium text-gray-800">{{ formato(convenio.codigo_convenio) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Aseguradora</p><p class="text-sm font-medium text-gray-800">{{ formato(convenio.aseguradora) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Fecha Inicio</p><p class="text-sm font-medium text-gray-800">{{ formato(convenio.fecha_inicio) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Fecha Fin</p><p class="text-sm font-medium text-gray-800">{{ formato(convenio.fecha_fin) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Estatus</p><Badge :variant="convenio.estatus === 'vigente' ? 'success' : 'warning'">{{ formato(convenio.estatus) }}</Badge></div>
          <div><p class="text-xs text-gray-500 uppercase">Renovación Automática</p><p class="text-sm font-medium text-gray-800">{{ siNo(convenio.renovacion_automatica) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Exclusivo</p><p class="text-sm font-medium text-gray-800">{{ siNo(convenio.exclusivo) }}</p></div>
        </div>
      </div>

      <!-- Pagos y Facturación -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Pagos y Facturación</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase">Plazo de Pago (días)</p><p class="text-sm font-medium text-gray-800">{{ formato(convenio.dias_credito) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Periodicidad de Corte</p><p class="text-sm font-medium text-gray-800">{{ formato(convenio.periodicidad_corte) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Aviso Previo Terminación</p><p class="text-sm font-medium text-gray-800">{{ formato(convenio.aviso_previo_terminacion_dias) }} días</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Requiere Folio CFDI</p><p class="text-sm font-medium text-gray-800">{{ siNo(convenio.requiere_folio_cfdi) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">IVA Incluido</p><p class="text-sm font-medium text-gray-800">{{ siNo(convenio.iva_incluido) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase">Tope de Crédito</p><p class="text-sm font-medium text-gray-800">{{ convenio.tope_credito ? '$' + parseFloat(convenio.tope_credito).toFixed(2) : '—' }}</p></div>
        </div>
      </div>

      <!-- Tarifas del Convenio -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Tarifas del Convenio</h3>
        <div v-if="!convenio.tarifas || convenio.tarifas.length === 0" class="text-sm text-gray-400 text-center py-4">Sin tarifas registradas</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Servicio</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Alcance</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Banderazo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">KM Incl.</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">KM Extra</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rec. Noct.</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rec. Dom</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Desc.</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="t in convenio.tarifas" :key="t.id" class="hover:bg-white/30">
                <td class="px-4 py-3 text-sm">{{ formato(t.servicio) }}</td>
                <td class="px-4 py-3 text-sm">{{ formato(t.alcance) }}</td>
                <td class="px-4 py-3 text-sm">{{ t.banderazo ? '$'+parseFloat(t.banderazo).toFixed(2) : '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ formato(t.km_incluidos) }}</td>
                <td class="px-4 py-3 text-sm">{{ t.costo_km_extra ? '$'+parseFloat(t.costo_km_extra).toFixed(2) : '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ t.tarifa_nocturna_recargo_pct ? t.tarifa_nocturna_recargo_pct+'%' : '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ t.tarifa_domingo_festivo_recargo_pct ? t.tarifa_domingo_festivo_recargo_pct+'%' : '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ t.descuento_pct ? t.descuento_pct+'%' : '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

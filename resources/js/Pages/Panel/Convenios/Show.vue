<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
  convenio: { type: Object, default: () => ({
    id: 1,
    nombre: 'Convenio General Atlas',
    aseguradora: 'Seguros Atlas',
    tipo_servicio: 'Transporte Local',
    fecha_inicio: '01 Ene 2026',
    fecha_fin: '31 Dic 2026',
    estatus: 'activo',
    descripcion: 'Convenio general para servicios de transporte local con cobertura básica.',
  })},
})

const tabActivo = ref('datos_generales')
const tabs = [
  { key: 'datos_generales', label: 'Datos Generales' },
  { key: 'tarifa', label: 'Tarifa' },
  { key: 'coberturas', label: 'Coberturas' },
  { key: 'unidades', label: 'Unidades Autorizadas' },
  { key: 'maniobras', label: 'Maniobras' },
  { key: 'facturacion', label: 'Facturación' },
]

const datosTarifa = {
  tarifa_base: '$850.00',
  costo_km: '$12.50',
  banderazo: '$150.00',
  costo_hora_espera: '$200.00',
  minimo_facturable: '$500.00',
}

const coberturas = [
  { concepto: 'Responsabilidad Civil', monto: '$500,000', aplica: true },
  { concepto: 'Daños a Carga', monto: '$200,000', aplica: true },
  { concepto: 'Robo Total', monto: '$150,000', aplica: true },
  { concepto: 'Robo Parcial', monto: '$75,000', aplica: false },
]

const unidadesAutorizadas = [
  { tipo: 'Camión 3.5 Ton', placas: 'ABC-1234', modelo: '2020', capacidad: '3.5 ton' },
  { tipo: 'Camión 8 Ton', placas: 'DEF-5678', modelo: '2021', capacidad: '8 ton' },
  { tipo: 'Camión Refrigerado', placas: 'GHI-9012', modelo: '2022', capacidad: '5 ton' },
]

const maniobras = [
  { nombre: 'Carga con montacargas', costo: '$350.00', incluido: true },
  { nombre: 'Descarga manual', costo: '$250.00', incluido: true },
  { nombre: 'Embalaje especializado', costo: '$500.00', incluido: false },
]

const datosFacturacion = {
  regimen_fiscal: 'Persona Moral',
  cfdi: 'Gastos en General',
  uso_cfdi: 'D03',
  forma_pago: 'Transferencia Electrónica',
  metodo_pago: 'PUE',
  dias_credito: 30,
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ convenio.nombre }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalles del convenio</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.convenios.index'))">
            Volver
          </NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.convenios.edit', convenio.id))">
            Editar
          </NeumorphicButton>
        </div>
      </div>

      <div class="flex flex-wrap gap-2">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="tabActivo = tab.key"
          class="rounded-xl px-5 py-2 text-sm font-medium transition-all duration-200"
          :class="tabActivo === tab.key
            ? 'bg-[#EEF2F7] text-[#4F46E5] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]'
            : 'bg-transparent text-gray-500 hover:text-gray-700'"
        >
          {{ tab.label }}
        </button>
      </div>

      <div v-if="tabActivo === 'datos_generales'" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Nombre</p>
            <p class="text-sm font-medium text-gray-800">{{ convenio.nombre }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Aseguradora</p>
            <p class="text-sm font-medium text-gray-800">{{ convenio.aseguradora }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Tipo Servicio</p>
            <p class="text-sm font-medium text-gray-800">{{ convenio.tipo_servicio }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Fecha Inicio</p>
            <p class="text-sm font-medium text-gray-800">{{ convenio.fecha_inicio }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Fecha Fin</p>
            <p class="text-sm font-medium text-gray-800">{{ convenio.fecha_fin }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Estatus</p>
            <Badge variant="success">{{ convenio.estatus }}</Badge>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-[#d0d5da]/30">
          <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Descripción</p>
          <p class="text-sm text-gray-700">{{ convenio.descripcion }}</p>
        </div>
      </div>

      <div v-if="tabActivo === 'tarifa'" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="(val, key) in datosTarifa" :key="key">
            <p class="text-xs text-gray-500 uppercase tracking-wider capitalize">{{ key.replace(/_/g, ' ') }}</p>
            <p class="text-sm font-medium text-gray-800">{{ val }}</p>
          </div>
        </div>
      </div>

      <div v-if="tabActivo === 'coberturas'" class="rounded-3xl bg-[#EEF2F7] overflow-hidden shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Concepto</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Monto</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Aplica</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="(cov, i) in coberturas" :key="i" class="hover:bg-white/30 transition-colors">
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ cov.concepto }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ cov.monto }}</td>
                <td class="px-4 py-3 text-sm">
                  <Badge :variant="cov.aplica ? 'success' : 'neutral'">{{ cov.aplica ? 'Sí' : 'No' }}</Badge>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="tabActivo === 'unidades'" class="rounded-3xl bg-[#EEF2F7] overflow-hidden shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Tipo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Placas</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Modelo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Capacidad</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="(uni, i) in unidadesAutorizadas" :key="i" class="hover:bg-white/30 transition-colors">
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ uni.tipo }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ uni.placas }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ uni.modelo }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ uni.capacidad }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="tabActivo === 'maniobras'" class="rounded-3xl bg-[#EEF2F7] overflow-hidden shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Costo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Incluido</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="(man, i) in maniobras" :key="i" class="hover:bg-white/30 transition-colors">
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ man.nombre }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ man.costo }}</td>
                <td class="px-4 py-3 text-sm">
                  <Badge :variant="man.incluido ? 'success' : 'neutral'">{{ man.incluido ? 'Sí' : 'No' }}</Badge>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="tabActivo === 'facturacion'" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="(val, key) in datosFacturacion" :key="key">
            <p class="text-xs text-gray-500 uppercase tracking-wider capitalize">{{ key.replace(/_/g, ' ') }}</p>
            <p class="text-sm font-medium text-gray-800">{{ val }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

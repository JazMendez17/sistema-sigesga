<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
  cliente: { type: Object, default: () => ({
    id: 1,
    nombre: 'Juan Pérez',
    telefono: '555-123-4567',
    email: 'juan@example.com',
    calle: 'Av. Reforma #123',
    colonia: 'Colonia Centro',
    ciudad: 'Ciudad de México',
    estado: 'CDMX',
    cp: '06600',
    aseguradora: 'Seguros Atlas',
    poliza: 'POL-00124',
    tipo_seguro: 'Cobertura Amplia',
    vigencia: '31 Dic 2026',
  })},
})

const tabActivo = ref('cotizaciones')
const tabs = [
  { key: 'cotizaciones', label: 'Historial de Cotizaciones' },
  { key: 'servicios', label: 'Historial de Servicios' },
]

const historialCotizaciones = [
  { folio: 'COT-00124', tipo: 'Transporte Local', origen: 'Centro', destino: 'Norte', total: '$1,200', estatus: 'pendiente', fecha: '23 Jul 2026' },
  { folio: 'COT-00120', tipo: 'Transporte de Maquinaria', origen: 'Industrial Vallejo', destino: 'Cuautitlán', total: '$12,000', estatus: 'aprobada', fecha: '19 Jul 2026' },
  { folio: 'COT-00115', tipo: 'Mudanza Residencial', origen: 'Colonia del Valle', destino: 'Santa Fe', total: '$5,500', estatus: 'rechazada', fecha: '10 Jul 2026' },
]

const historialServicios = [
  { folio: 'SVC-00075', tipo: 'Transporte Local', operador: 'Roberto Méndez', destino: 'Zona Industrial', fecha: '15 Jul 2026', estatus: 'finalizado' },
  { folio: 'SVC-00062', tipo: 'Mudanza Residencial', operador: 'Laura Torres', destino: 'Santa Fe', fecha: '28 Jun 2026', estatus: 'finalizado' },
]
</script>

<template>
  <!-- Perfil detallado del cliente -->
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado con acciones -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ cliente.nombre }}</h1>
          <p class="text-sm text-gray-500 mt-1">Perfil del cliente</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.clientes.index'))">
            Volver
          </NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.clientes.edit', cliente.id))">
            Editar
          </NeumorphicButton>
        </div>
      </div>

      <!-- Tarjetas de información del cliente -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Datos personales -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos Personales</h3>
          <div class="space-y-3">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Nombre</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.nombre }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Teléfono</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.telefono }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Email</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.email }}</p>
            </div>
          </div>
        </div>

        <!-- Dirección del cliente -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Dirección</h3>
          <div class="space-y-3">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Calle</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.calle }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Colonia</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.colonia }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Ciudad / Estado</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.ciudad }}, {{ cliente.estado }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Código Postal</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.cp }}</p>
            </div>
          </div>
        </div>

        <!-- Aseguradora y póliza -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Aseguradora / Póliza</h3>
          <div class="space-y-3">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Aseguradora</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.aseguradora }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Póliza</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.poliza }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Tipo de Seguro</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.tipo_seguro }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Vigencia</p>
              <p class="text-sm font-medium text-gray-800">{{ cliente.vigencia }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Pestañas de historial -->
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

      <!-- Tabla de historial de cotizaciones -->
      <div v-if="tabActivo === 'cotizaciones'" class="rounded-3xl bg-[#EEF2F7] overflow-hidden shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Folio</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Tipo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Origen</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Destino</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Total</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Estatus</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Fecha</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="(item, i) in historialCotizaciones" :key="i" class="hover:bg-white/30 transition-colors">
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.folio }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.tipo }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.origen }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.destino }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.total }}</td>
                <td class="px-4 py-3 text-sm"><Badge :variant="item.estatus">{{ item.estatus }}</Badge></td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.fecha }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tabla de historial de servicios -->
      <div v-if="tabActivo === 'servicios'" class="rounded-3xl bg-[#EEF2F7] overflow-hidden shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Folio</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Tipo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Operador</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Destino</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Estatus</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="(item, i) in historialServicios" :key="i" class="hover:bg-white/30 transition-colors">
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.folio }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.tipo }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.operador }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.destino }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ item.fecha }}</td>
                <td class="px-4 py-3 text-sm"><Badge :variant="item.estatus">{{ item.estatus }}</Badge></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

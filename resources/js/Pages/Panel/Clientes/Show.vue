<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
  cliente: { type: Object, required: true },
})

const tabActivo = ref('cotizaciones')
const tabs = [
  { key: 'cotizaciones', label: 'Cotizaciones' },
  { key: 'servicios', label: 'Servicios' },
]

function formato(val) {
  return val || '—'
}
</script>

<template>
  <!-- Perfil detallado del cliente -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ cliente.nombre_completo || cliente.nombre }}</h1>
          <p class="text-sm text-gray-500 mt-1">Perfil del cliente</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.clientes.index'))">Volver</NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.clientes.edit', cliente.id))">Editar</NeumorphicButton>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Datos personales -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos Personales</h3>
          <div class="space-y-3">
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Tipo de Cliente</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.tipo_cliente) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Nombre Completo</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.nombre_completo) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Sexo</p><p class="text-sm font-medium text-gray-800">{{ cliente.sexo === 'M' ? 'Masculino' : cliente.sexo === 'F' ? 'Femenino' : '—' }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">CURP</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.curp) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Fecha de Nacimiento</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.fecha_nacimiento) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Nacionalidad</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.nacionalidad) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Folio INE</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.folio_ine) }}</p></div>
          </div>
        </div>

        <!-- Contacto -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Contacto</h3>
          <div class="space-y-3">
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Teléfono</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.telefono) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Teléfono Local</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.telefono_local) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Email</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.email) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Contacto Enlace</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.contacto_enlace) }}</p></div>
          </div>
        </div>

        <!-- Aseguradora -->
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Aseguradora / Póliza</h3>
          <div class="space-y-3">
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Aseguradora</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.aseguradora_comercial) || formato(cliente.aseguradora) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Razón Social</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.aseguradora) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Número de Póliza</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.numero_poliza) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Tipo de Cobertura</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.tipo_cobertura_poliza) }}</p></div>
          </div>
        </div>

        <!-- Convenio y Tarifas -->
        <div v-if="cliente.convenio" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Convenio y Tarifas</h3>
          <div class="space-y-3">
            <div><p class="text-xs text-gray-500 uppercase">Convenio</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.convenio.nombre) }} <span class="text-xs text-gray-400">({{ formato(cliente.convenio.codigo) }})</span></p></div>
            <div><p class="text-xs text-gray-500 uppercase">Plazo de Pago</p><p class="text-sm font-medium text-gray-800">{{ cliente.convenio.dias_credito ? cliente.convenio.dias_credito + ' días' : '—' }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Cubre Casetas</p><p class="text-sm font-medium" :class="cliente.convenio.cubre_casetas ? 'text-green-600' : 'text-gray-400'">{{ cliente.convenio.cubre_casetas ? 'Sí' : 'No' }}</p></div>
            <div v-if="cliente.convenio.tarifas?.length" class="pt-2 border-t border-gray-200">
              <p class="text-xs text-gray-500 uppercase mb-2">Tarifas del Convenio</p>
              <div class="overflow-x-auto"><table class="w-full text-sm">
                <thead><tr class="border-b"><th class="text-left py-1 text-xs text-gray-400">Servicio</th><th class="text-right py-1 text-xs text-gray-400">Banderazo</th><th class="text-right py-1 text-xs text-gray-400">KM Incl</th><th class="text-right py-1 text-xs text-gray-400">KM Extra</th></tr></thead>
                <tbody><tr v-for="t in cliente.convenio.tarifas" :key="t.servicio" class="border-b border-gray-100"><td class="py-1">{{ t.servicio }}</td><td class="text-right py-1">${{ t.banderazo?.toFixed(2) }}</td><td class="text-right py-1">{{ t.km_incluidos }}</td><td class="text-right py-1">${{ t.costo_km_extra?.toFixed(2) }}</td></tr></tbody>
              </table></div>
            </div>
          </div>
        </div>

        <!-- Dirección -->
        <div v-if="cliente.direccion" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] lg:col-span-3">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Dirección</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Calle</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.direccion.calle) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Número</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.direccion.numero) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Colonia</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.direccion.colonia) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Ciudad</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.direccion.ciudad) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase tracking-wider">Estado</p><p class="text-sm font-medium text-gray-800">{{ formato(cliente.direccion.estado) }}</p></div>
          </div>
        </div>
      </div>

      <!-- Pestañas -->
      <div class="flex flex-wrap gap-2">
        <button v-for="tab in tabs" :key="tab.key" @click="tabActivo = tab.key"
          class="rounded-xl px-5 py-2 text-sm font-medium transition-all duration-200"
          :class="tabActivo === tab.key ? 'bg-[#EEF2F7] text-[#4F46E5] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]' : 'bg-transparent text-gray-500 hover:text-gray-700'">
          {{ tab.label }}
        </button>
      </div>

      <!-- Cotizaciones -->
      <div v-if="tabActivo === 'cotizaciones' && cliente.cotizaciones" class="rounded-3xl bg-[#EEF2F7] overflow-hidden shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Folio</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Monto</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estatus</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="(c, i) in cliente.cotizaciones" :key="i" class="hover:bg-white/30">
                <td class="px-4 py-3 text-sm">{{ c.folio }}</td>
                <td class="px-4 py-3 text-sm">{{ formato(c.tipo) }}</td>
                <td class="px-4 py-3 text-sm">${{ c.monto?.toFixed(2) }}</td>
                <td class="px-4 py-3 text-sm">{{ formato(c.fecha) }}</td>
                <td class="px-4 py-3 text-sm"><Badge :variant="c.estatus">{{ c.estatus }}</Badge></td>
              </tr>
              <tr v-if="!cliente.cotizaciones.length">
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Sin cotizaciones registradas</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

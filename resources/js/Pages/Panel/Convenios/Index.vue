<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import Badge from '@/Components/Badge.vue'

const filtroActivo = ref('todos')
const filtros = [
  { key: 'todos', label: 'Todos' },
  { key: 'aseguradoras', label: 'Aseguradoras' },
  { key: 'tarifas', label: 'Tarifas' },
  { key: 'tipo_cobertura', label: 'Tipo de Cobertura' },
  { key: 'tipo_servicio', label: 'Tipo de Servicio' },
]
const aseguradoraFiltro = ref('')
const coberturaFiltro = ref('')
const servicioFiltro = ref('')
const tarifaFiltro = ref('')
const busqueda = ref('')

const columns = [
  { key: 'codigo', label: 'Código' },
  { key: 'nombre', label: 'Nombre' },
  { key: 'aseguradora', label: 'Aseguradora' },
  { key: 'fecha_inicio', label: 'Fecha Inicio' },
  { key: 'fecha_fin', label: 'Fecha Fin' },
  { key: 'estatus', label: 'Estatus' },
]

const page = usePage()
const convenios = computed(() => page.props.convenios || [])
const tarifasGlobales = computed(() => page.props.tarifasGlobales || [])
const aseguradoras = computed(() => page.props.aseguradoras || [])
const tiposServicio = computed(() => page.props.tiposServicio || [])
const tiposCobertura = computed(() => page.props.tiposCobertura || [])

function resetFiltros() {
  aseguradoraFiltro.value = ''
  coberturaFiltro.value = ''
  servicioFiltro.value = ''
  tarifaFiltro.value = ''
}

const tarifasFiltradas = computed(() => {
  let result = tarifasGlobales.value
  if (tarifaFiltro.value === 'con') result = result
  else if (tarifaFiltro.value === 'sin') result = []
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    result = result.filter(t =>
      t.convenio?.toLowerCase().includes(q) ||
      t.servicio?.toLowerCase().includes(q) ||
      t.alcance?.toLowerCase().includes(q)
    )
  }
  return result
})

const filtrados = computed(() => {
  let resultado = convenios.value
  if (filtroActivo.value === 'aseguradoras' && aseguradoraFiltro.value) {
    resultado = resultado.filter(c => c.aseguradora === aseguradoraFiltro.value)
  } else if (filtroActivo.value === 'tarifas') {
    if (tarifaFiltro.value === 'con') resultado = resultado.filter(c => c.tiene_tarifas)
    else if (tarifaFiltro.value === 'sin') resultado = resultado.filter(c => !c.tiene_tarifas)
  } else if (filtroActivo.value === 'tipo_cobertura' && coberturaFiltro.value) {
    resultado = resultado.filter(c => c.tipo_cobertura === coberturaFiltro.value)
  } else if (filtroActivo.value === 'tipo_servicio' && servicioFiltro.value) {
    resultado = resultado.filter(c => c.tipo_servicio === servicioFiltro.value)
  }
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    resultado = resultado.filter(c => c.nombre?.toLowerCase().includes(q) || c.codigo?.toLowerCase().includes(q))
  }
  return resultado
})

function eliminarConvenio(id) {
  if (confirm('¿Eliminar este convenio?')) {
    router.delete(route('panel.convenios.destroy', { id }))
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Convenios</h1>
        <NeumorphicButton @click="router.visit(route('panel.convenios.create'))">+ Nuevo Convenio</NeumorphicButton>
      </div>

      <div class="flex flex-wrap gap-2">
        <button v-for="filtro in filtros" :key="filtro.key" @click="filtroActivo = filtro.key; resetFiltros()"
          class="rounded-xl px-5 py-2 text-sm font-medium transition-all duration-200"
          :class="filtroActivo === filtro.key ? 'bg-[#EEF2F7] text-[#4F46E5] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]' : 'bg-transparent text-gray-500 hover:text-gray-700'">
          {{ filtro.label }}
        </button>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <select v-if="filtroActivo === 'aseguradoras'" v-model="aseguradoraFiltro"
          class="w-full sm:w-64 appearance-none bg-[#E8EDF2] rounded-2xl py-2.5 px-4 text-sm shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
          <option value="">Todas las aseguradoras</option>
          <option v-for="a in aseguradoras" :key="a.id" :value="a.nombre">{{ a.nombre }}</option>
        </select>
        <select v-if="filtroActivo === 'tarifas'" v-model="tarifaFiltro"
          class="w-full sm:w-64 appearance-none bg-[#E8EDF2] rounded-2xl py-2.5 px-4 text-sm shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
          <option value="">Todos</option>
          <option value="con">Con tarifas</option>
          <option value="sin">Sin tarifas</option>
        </select>
        <select v-if="filtroActivo === 'tipo_cobertura'" v-model="coberturaFiltro"
          class="w-full sm:w-64 appearance-none bg-[#E8EDF2] rounded-2xl py-2.5 px-4 text-sm shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
          <option value="">Todas las coberturas</option>
          <option v-for="tc in tiposCobertura" :key="tc" :value="tc">{{ tc }}</option>
        </select>
        <select v-if="filtroActivo === 'tipo_servicio'" v-model="servicioFiltro"
          class="w-full sm:w-64 appearance-none bg-[#E8EDF2] rounded-2xl py-2.5 px-4 text-sm shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
          <option value="">Todos los servicios</option>
          <option v-for="ts in tiposServicio" :key="ts.id" :value="ts.nombre">{{ ts.nombre }}</option>
        </select>
        <NeumorphicInput v-if="filtroActivo === 'todos'" v-model="busqueda" placeholder="Buscar por nombre o código..." class="w-full sm:w-64" />
      </div>

      <!-- Tabla de tarifas globales -->
      <div v-if="filtroActivo === 'tarifas'" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Convenio</th>
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Servicio</th>
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Alcance</th>
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Banderazo</th>
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">KM Incl.</th>
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">KM Extra</th>
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rec. Noct.</th>
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rec. Dom</th>
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Desc.</th>
                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo Desc.</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="t in tarifasFiltradas" :key="t.id" class="hover:bg-white/30 text-sm">
                <td class="px-3 py-3">{{ t.convenio }}</td>
                <td class="px-3 py-3">{{ t.servicio }}</td>
                <td class="px-3 py-3">{{ t.alcance }}</td>
                <td class="px-3 py-3">{{ t.banderazo ? '$'+t.banderazo.toFixed(2) : '—' }}</td>
                <td class="px-3 py-3">{{ t.km_incluidos || '—' }}</td>
                <td class="px-3 py-3">{{ t.costo_km_extra ? '$'+t.costo_km_extra.toFixed(2) : '—' }}</td>
                <td class="px-3 py-3">{{ t.tarifa_nocturna_recargo_pct ? t.tarifa_nocturna_recargo_pct+'%' : '—' }}</td>
                <td class="px-3 py-3">{{ t.tarifa_domingo_festivo_recargo_pct ? t.tarifa_domingo_festivo_recargo_pct+'%' : '—' }}</td>
                <td class="px-3 py-3">{{ t.descuento_pct ? t.descuento_pct+'%' : '—' }}</td>
                <td class="px-3 py-3">{{ t.tipo_descuento || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tabla de convenios -->
      <div v-if="filtroActivo !== 'tarifas'" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="filtrados">
          <template #cell-estatus="{ row }">
            <Badge :variant="row.estatus === 'vigente' ? 'success' : row.estatus === 'vencido' || row.estatus === 'cancelado' ? 'danger' : 'warning'">
              {{ row.estatus === 'en_negociacion' ? 'En Negociación' : row.estatus }}
            </Badge>
          </template>
          <template #actions="{ row }">
            <div class="flex items-center gap-2">
              <button @click="router.visit(route('panel.convenios.show', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              </button>
              <button @click="router.visit(route('panel.convenios.edit', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#059669]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              </button>
              <button @click="eliminarConvenio(row.id)" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-red-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

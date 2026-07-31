<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const filtroActivo = ref('todos')
const filtros = ['todos', 'vigente', 'vencido', 'en_negociacion', 'cancelado']
const aseguradoraFiltro = ref('')

const columns = [
  { key: 'nombre', label: 'Nombre' },
  { key: 'aseguradora', label: 'Aseguradora' },
  { key: 'tipo_servicio', label: 'Tipo Servicio' },
  { key: 'tipo_ruta', label: 'Tipo Ruta' },
  { key: 'tipo_cobertura', label: 'Cobertura' },
  { key: 'estatus', label: 'Estatus' },
]

const page = usePage()
const convenios = computed(() => page.props.convenios || [])
const aseguradoras = computed(() => page.props.aseguradoras || [])

const filtrados = computed(() => {
  let resultado = convenios.value
  if (filtroActivo.value !== 'todos') {
    resultado = resultado.filter(c => c.estatus === filtroActivo.value)
  }
  if (aseguradoraFiltro.value) {
    resultado = resultado.filter(c => c.aseguradora === aseguradoraFiltro.value)
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
        <NeumorphicButton @click="router.visit(route('panel.convenios.create'))">
          + Nuevo Convenio
        </NeumorphicButton>
      </div>

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="filtro in filtros"
            :key="filtro"
            @click="filtroActivo = filtro"
            class="rounded-xl px-5 py-2 text-sm font-medium capitalize transition-all duration-200"
            :class="filtroActivo === filtro
              ? 'bg-[#EEF2F7] text-[#4F46E5] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]'
              : 'bg-transparent text-gray-500 hover:text-gray-700'"
          >
            {{ filtro === 'en_negociacion' ? 'En Negociación' : filtro === 'todos' ? 'Todos' : filtro }}
          </button>
        </div>
        <div class="relative">
          <select
            v-model="aseguradoraFiltro"
            class="w-full sm:w-56 appearance-none bg-[#E8EDF2] text-[#1F2937] rounded-2xl py-3 px-4 pr-10 text-sm shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300"
          >
            <option value="">Todas las aseguradoras</option>
            <option v-for="aseg in aseguradoras" :key="aseg.id" :value="aseg.nombre">{{ aseg.nombre }}</option>
          </select>
          <svg class="absolute inset-y-0 right-3 my-auto h-4 w-4 text-[#6B7280] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </div>
      </div>

      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="filtrados">
          <template #cell-estatus="{ row }">
            <Badge
              :variant="row.estatus === 'vigente' ? 'success' : row.estatus === 'vencido' || row.estatus === 'cancelado' ? 'danger' : 'warning'"
            >
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

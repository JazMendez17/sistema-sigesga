<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import Badge from '@/Components/Badge.vue'

const filtroActivo = ref('todos')
const busqueda = ref('')

const filtros = ['todos', 'asignado', 'en_curso', 'finalizado', 'cancelado']

const columns = [
  { key: 'folio', label: 'Folio' },
  { key: 'cliente', label: 'Cliente' },
  { key: 'tipo', label: 'Tipo Servicio' },
  { key: 'operador', label: 'Operador' },
  { key: 'origen', label: 'Origen' },
  { key: 'destino', label: 'Destino' },
  { key: 'fecha', label: 'Fecha' },
  { key: 'estatus', label: 'Estatus' },
  { key: 'acciones', label: 'Acciones' },
]

const page = usePage()
const servicios = computed(() => page.props.servicios || [])

const etiquetaFiltro = (f) => {
  const map = { todos: 'Todos', asignado: 'Asignado', en_curso: 'En Curso', finalizado: 'Finalizado', cancelado: 'Cancelado' }
  return map[f] || f
}

const estadosActivos = ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino']

const filteredServicios = computed(() => {
  let result = servicios.value
  if (filtroActivo.value !== 'todos') {
    if (filtroActivo.value === 'en_curso') {
      result = result.filter(s => estadosActivos.includes(s.estatus))
    } else {
      result = result.filter(s => s.estatus === filtroActivo.value)
    }
  }
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    result = result.filter(s => s.cliente?.toLowerCase().includes(q) || s.folio?.toLowerCase().includes(q))
  }
  return result
})

function eliminarServicio(id) {
  if (confirm('¿Eliminar este servicio?')) {
    router.delete(route('panel.servicios.destroy', { id }))
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Servicios</h1>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.reportes.index'))">
            Reportes
          </NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.servicios.create'))">
            + Nuevo Servicio
          </NeumorphicButton>
        </div>
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
            {{ etiquetaFiltro(filtro) }}
          </button>
        </div>
        <NeumorphicInput
          v-model="busqueda"
          placeholder="Buscar servicio..."
          class="w-full sm:w-64"
        />
      </div>

      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="filteredServicios">
          <template #cell-estatus="{ row }">
            <Badge :variant="row.estatus === 'finalizado' ? 'success' : row.estatus === 'cancelado' ? 'danger' : estadosActivos.includes(row.estatus) ? 'warning' : 'neutral'">
              {{ row.estatus === 'asignado' ? 'Asignado' : row.estatus === 'inicio_servicio' ? 'Inicio Servicio' : row.estatus === 'en_sitio_origen' ? 'En Sitio Origen' : row.estatus === 'salida_destino' ? 'Salida a Destino' : row.estatus === 'en_destino' ? 'En Destino' : row.estatus === 'finalizado' ? 'Finalizado' : row.estatus === 'solicitud_cancelacion' ? 'Solicitud Cancelación' : row.estatus === 'cancelado' ? 'Cancelado' : row.estatus }}
            </Badge>
          </template>
          <template #cell-acciones="{ row }">
            <div class="flex items-center gap-2">
              <button @click="router.visit(route('panel.servicios.show', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              </button>
              <button @click="router.visit(route('panel.servicios.edit', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#059669]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              </button>
              <button @click="eliminarServicio(row.id)" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-red-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </template>
        </DataTable>
      </div>

      <div class="flex items-center justify-center gap-2">
        <button class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#EEF2F7] text-gray-500 shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] transition-all hover:text-gray-700">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <span class="text-sm text-gray-500">Página 1</span>
        <button class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#EEF2F7] text-gray-500 shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] transition-all hover:text-gray-700">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>
      </div>
    </div>
  </AppLayout>
</template>

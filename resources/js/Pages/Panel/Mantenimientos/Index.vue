<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import Badge from '@/Components/Badge.vue'

const filtroUnidad = ref('')

const page = usePage()
const mantenimientos = computed(() => page.props.mantenimientos || [])
const alertas = computed(() => page.props.alertas || [])
const unidades = computed(() => page.props.unidades || [])

const mantenimientosFiltrados = computed(() =>
  mantenimientos.value.filter(m =>
    !filtroUnidad.value || m.unidad?.toLowerCase().includes(filtroUnidad.value.toLowerCase())
  )
)

const columns = [
  { key: 'unidad', label: 'Unidad' },
  { key: 'tipo', label: 'Tipo' },
  { key: 'fecha', label: 'Fecha' },
  { key: 'kilometraje', label: 'Kilometraje' },
  { key: 'costo', label: 'Costo' },
  { key: 'proximo_mantenimiento_fecha', label: 'Próx. Mant. (Fecha)' },
  { key: 'proximo_mantenimiento_km', label: 'Próx. Mant. (Km)' },
]

function eliminarMantenimiento(id) {
  if (confirm('¿Eliminar este mantenimiento?')) {
    router.delete(route('panel.mantenimientos.destroy', { id }))
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Mantenimientos</h1>
        <NeumorphicButton @click="router.visit(route('panel.mantenimientos.create'))">+ Nuevo Mantenimiento</NeumorphicButton>
      </div>

      <div v-if="alertas.length" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-red-600 flex items-center gap-2">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
          Mantenimientos Próximos a Vencer
        </h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="alerta in alertas"
            :key="alerta.unidad + alerta.tipo"
            class="rounded-2xl border-l-4 border-red-500 bg-[#E8EDF2] p-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff]"
          >
            <p class="text-sm font-semibold text-gray-800">{{ alerta.unidad }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ alerta.tipo }}</p>
            <div class="mt-2 flex items-center justify-between">
              <span class="text-xs text-gray-500">Vence: {{ alerta.vence }}</span>
              <Badge variant="danger">{{ alerta.dias }} días</Badge>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <NeumorphicInput
          v-model="filtroUnidad"
          placeholder="Filtrar por unidad..."
          class="w-full sm:w-64"
        />
      </div>

      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="mantenimientosFiltrados">
          <template #cell-costo="{ row }">
            ${{ row.costo?.toFixed(2) }}
          </template>
          <template #actions="{ row }">
            <div class="flex items-center gap-2">
              <button @click="router.visit(route('panel.mantenimientos.edit', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#059669]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              </button>
              <button @click="eliminarMantenimiento(row.id)" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-red-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

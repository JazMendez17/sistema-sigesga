<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const columns = [
  { key: 'nombre', label: 'Nombre' },
  { key: 'numero_licencia', label: 'Licencia' },
  { key: 'fecha_vigencia', label: 'Vigencia' },
  { key: 'disponible', label: 'Disponible' },
]

const page = usePage()
const operadores = computed(() => page.props.operadores || [])
const licenciasProximas = computed(() => page.props.licenciasProximas || [])

function eliminarOperador(id) {
  if (confirm('¿Eliminar este operador?')) {
    router.delete(route('panel.operadores.destroy', { id }))
  }
}
</script>

<template>
  <!-- Listado de operadores con alertas de licencias próximas a vencer -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Operadores</h1>
        <NeumorphicButton @click="router.visit(route('panel.operadores.create'))">+ Nuevo Operador</NeumorphicButton>
      </div>

      <div v-if="licenciasProximas.length" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-red-600 flex items-center gap-2">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
          Licencias Próximas a Vencer
        </h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="lic in licenciasProximas"
            :key="lic.nombre"
            class="rounded-2xl border-l-4 border-red-500 bg-[#E8EDF2] p-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff]"
          >
            <p class="text-sm font-semibold text-gray-800">{{ lic.nombre }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ lic.licencia }}</p>
            <div class="mt-2 flex items-center justify-between">
              <span class="text-xs text-gray-500">Vence: {{ lic.vigencia }}</span>
              <Badge variant="danger">{{ lic.dias }} días</Badge>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="operadores">
          <template #cell-disponible="{ row }">
            <Badge :variant="row.disponible ? 'success' : 'neutral'">{{ row.disponible ? 'Disponible' : 'No Disponible' }}</Badge>
          </template>
          <template #actions="{ row }">
            <div class="flex items-center gap-2">
              <button @click="router.visit(route('panel.operadores.show', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              </button>
              <button @click="router.visit(route('panel.operadores.edit', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#059669]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              </button>
              <button @click="eliminarOperador(row.id)" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-red-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

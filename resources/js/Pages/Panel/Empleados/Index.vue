<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const busqueda = ref('')

const page = usePage()
const empleados = computed(() => page.props.empleados || [])

const empleadosFiltrados = computed(() =>
  empleados.value.filter(e =>
    !busqueda.value || e.nombre_completo?.toLowerCase().includes(busqueda.value.toLowerCase()) || e.curp?.toLowerCase().includes(busqueda.value.toLowerCase())
  )
)

const columns = [
  { key: 'nombre_completo', label: 'Nombre Completo' },
  { key: 'curp', label: 'CURP' },
  { key: 'telefono', label: 'Teléfono' },
  { key: 'puesto', label: 'Puesto' },
  { key: 'oficina', label: 'Oficina' },
]

function eliminarEmpleado(id) {
  if (confirm('¿Eliminar este empleado?')) {
    router.delete(route('panel.empleados.destroy', { id }))
  }
}
</script>

<template>
  <!-- Listado de empleados registrados -->
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado con botón de nuevo empleado -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Empleados</h1>
        <NeumorphicButton @click="router.visit(route('panel.empleados.create'))">+ Nuevo Empleado</NeumorphicButton>
      </div>

      <!-- Barra de búsqueda -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <NeumorphicInput
          v-model="busqueda"
          placeholder="Buscar empleado..."
          class="w-full sm:w-64"
        />
      </div>

      <!-- Tabla de empleados -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="empleadosFiltrados">
          <template #actions="{ row }">
            <div class="flex items-center gap-2">
              <button @click="router.visit(route('panel.empleados.show', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              </button>
              <button @click="router.visit(route('panel.empleados.edit', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#059669]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              </button>
              <button @click="eliminarEmpleado(row.id)" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-red-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

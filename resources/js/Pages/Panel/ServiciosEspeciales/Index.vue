<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const busqueda = ref('')
const columns = [
  { key: 'convenio', label: 'Convenio' },
  { key: 'aseguradora', label: 'Aseguradora' },
  { key: 'concepto', label: 'Concepto' },
  { key: 'forma_cobro', label: 'Forma de Cobro' },
  { key: 'costo', label: 'Costo' },
]

const page = usePage()
const maniobras = computed(() => page.props.maniobras || [])

const filtradas = computed(() => {
  if (!busqueda.value) return maniobras.value
  const q = busqueda.value.toLowerCase()
  return maniobras.value.filter(m =>
    m.convenio?.toLowerCase().includes(q) ||
    m.concepto?.toLowerCase().includes(q)
  )
})

function eliminar(id) {
  if (confirm('¿Eliminar esta maniobra?')) {
    router.delete(route('panel.servicios-especiales.destroy', { id }))
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Servicios Especiales</h1>
        <NeumorphicButton @click="router.visit(route('panel.servicios-especiales.create'))">+ Nueva Maniobra</NeumorphicButton>
      </div>
      <NeumorphicInput v-model="busqueda" placeholder="Buscar..." class="w-full sm:w-64" />
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="filtradas">
          <template #cell-costo="{ row }">${{ row.costo?.toFixed(2) }}</template>
          <template #cell-aplica="{ row }">
            <span :class="row.aplica ? 'text-green-600' : 'text-gray-400'" class="text-xs font-medium">{{ row.aplica ? 'Activo' : 'Inactivo' }}</span>
          </template>
          <template #actions="{ row }">
            <div class="flex items-center gap-2">
              <button @click="router.visit(route('panel.servicios-especiales.edit', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#059669]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              </button>
              <button @click="eliminar(row.id)" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-red-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

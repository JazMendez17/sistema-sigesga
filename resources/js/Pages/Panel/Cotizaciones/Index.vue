<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import Badge from '@/Components/Badge.vue'

const filtroActivo = ref('todas')
const busqueda = ref('')

const filtros = ['todas', 'pendientes', 'aprobadas', 'rechazadas']

const columns = [
  { key: 'folio', label: 'Folio' },
  { key: 'cliente', label: 'Cliente' },
  { key: 'tipo', label: 'Tipo Servicio' },
  { key: 'origen', label: 'Origen' },
  { key: 'destino', label: 'Destino' },
  { key: 'total', label: 'Total' },
  { key: 'estatus', label: 'Estatus' },
  { key: 'fecha', label: 'Fecha' },
]

const page = usePage()
const cotizaciones = computed(() => page.props.cotizaciones || [])

const filteredCotizaciones = computed(() => {
  let result = cotizaciones.value
  if (filtroActivo.value !== 'todas') {
    result = result.filter(c => c.estatus === filtroActivo.value)
  }
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    result = result.filter(c => c.cliente?.toLowerCase().includes(q) || c.folio?.toLowerCase().includes(q))
  }
  return result
})
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Cotizaciones</h1>
        <NeumorphicButton @click="router.visit(route('panel.cotizaciones.create'))">
          + Nueva Cotización
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
            {{ filtro === 'todas' ? 'Todas' : filtro }}
          </button>
        </div>
        <NeumorphicInput
          v-model="busqueda"
          placeholder="Buscar cotización..."
          class="w-full sm:w-64"
        />
      </div>

      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="filteredCotizaciones" @rowClick="(row) => router.visit(route('panel.cotizaciones.show', { id: row.id }))">
          <template #cell-estatus="{ row }">
            <Badge :variant="row.estatus">{{ row.estatus }}</Badge>
          </template>
          <template #cell-total="{ row }">
            ${{ row.total?.toFixed(2) }}
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

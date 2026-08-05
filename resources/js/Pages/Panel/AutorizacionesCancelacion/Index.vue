<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const busqueda = ref('')
const tabActivo = ref('pendientes')
const tabs = ['pendientes', 'aprobadas', 'rechazadas']

const columns = [
  { key: 'folio_servicio', label: 'Folio Servicio' },
  { key: 'solicitante', label: 'Solicitante' },
  { key: 'motivo', label: 'Motivo' },
  { key: 'tipo_incidencia', label: 'Tipo Incidencia' },
  { key: 'fecha', label: 'Fecha' },
  { key: 'estatus', label: 'Estatus' },
]

const page = usePage()
const autorizaciones = computed(() => page.props.autorizaciones || [])

const filtradas = computed(() => {
  const statusMap = { pendientes: 'pendiente', aprobadas: 'aprobada', rechazadas: 'rechazada' }
  let resultado = autorizaciones.value.filter(a => a.estatus === statusMap[tabActivo.value])
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    resultado = resultado.filter(a =>
      a.folio_servicio?.toLowerCase().includes(q) ||
      a.solicitante?.toLowerCase().includes(q) ||
      a.motivo?.toLowerCase().includes(q)
    )
  }
  return resultado
})

function aprobar(id) {
  router.post(route('panel.autorizaciones-cancelacion.aprobar', { id }))
}

function rechazar(id) {
  router.post(route('panel.autorizaciones-cancelacion.rechazar', { id }))
}
</script>

<template>
  <!-- Panel de autorizaciones de cancelación de servicios -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Autorizaciones de Cancelación</h1>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="tab in tabs"
            :key="tab"
            @click="tabActivo = tab"
            class="rounded-xl px-5 py-2 text-sm font-medium capitalize transition-all duration-200"
            :class="tabActivo === tab
              ? 'bg-[#EEF2F7] text-[#4F46E5] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]'
              : 'bg-transparent text-gray-500 hover:text-gray-700'"
          >
            {{ tab }}
          </button>
        </div>
        <NeumorphicInput
          v-model="busqueda"
          placeholder="Buscar autorización..."
          class="w-full sm:w-64"
        />
      </div>

      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="filtradas">
          <template #cell-estatus="{ row }">
            <Badge :variant="row.estatus">{{ row.estatus }}</Badge>
          </template>
          <template #actions="{ row }">
            <div class="flex items-center gap-2">
              <button v-if="row.estatus === 'pendiente'" @click="aprobar(row.id)" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#059669]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
              </button>
              <button v-if="row.estatus === 'pendiente'" @click="rechazar(row.id)" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-red-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import Badge from '@/Components/Badge.vue'

const busqueda = ref('')

const columns = [
  { key: 'folio', label: 'Folio Factura' },
  { key: 'cliente', label: 'Cliente' },
  { key: 'servicio', label: 'Servicio' },
  { key: 'subtotal', label: 'Subtotal' },
  { key: 'iva', label: 'IVA' },
  { key: 'total', label: 'Total' },
  { key: 'estatus', label: 'Estatus' },
  { key: 'fecha', label: 'Fecha' },
]

const page = usePage()
const facturas = computed(() => page.props.facturas || [])
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Facturación</h1>
        <NeumorphicButton disabled title="Próximamente">
          + Generar Factura
        </NeumorphicButton>
      </div>

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div></div>
        <NeumorphicInput
          v-model="busqueda"
          placeholder="Buscar factura..."
          class="w-full sm:w-64"
        />
      </div>

      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <DataTable :columns="columns" :data="facturas">
          <template #cell-estatus="{ row }">
            <Badge :variant="row.estatus === 'vigente' ? 'success' : 'danger'">{{ row.estatus }}</Badge>
          </template>
          <template #cell-subtotal="{ row }">
            ${{ row.subtotal?.toFixed(2) }}
          </template>
          <template #cell-iva="{ row }">
            ${{ row.iva?.toFixed(2) }}
          </template>
          <template #cell-total="{ row }">
            ${{ row.total?.toFixed(2) }}
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

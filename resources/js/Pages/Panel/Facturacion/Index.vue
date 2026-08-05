<script setup>
import { ref, computed } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import Badge from '@/Components/Badge.vue'

const busqueda = ref('')
const mostrarModal = ref(false)

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
const servicios = computed(() => page.props.servicios || [])

// Formulario para generar factura
const form = useForm({
  servicio_id: '',
  cliente_id: '',
  subtotal: '',
  iva: '',
  total: '',
  correo_envio_factura: '',
})

// Obtener cliente del servicio seleccionado
const servicioSeleccionado = computed(() => {
  return servicios.value.find(s => s.id == form.servicio_id)
})

function seleccionarServicio() {
  if (servicioSeleccionado.value) {
    form.cliente_id = servicioSeleccionado.value.cliente_id || ''
  }
}

function generarFactura() {
  form.post(route('panel.facturacion.store'), {
    onSuccess: () => {
      mostrarModal.value = false
      form.reset()
    },
  })
}

function enviarFactura(id) {
  if (confirm('¿Enviar esta factura por correo electrónico?')) {
    router.post(route('panel.facturacion.enviar', { id }))
  }
}

const filteredFacturas = computed(() => {
  if (!busqueda.value) return facturas.value
  const q = busqueda.value.toLowerCase()
  return facturas.value.filter(f =>
    f.folio?.toLowerCase().includes(q) ||
    f.cliente?.toLowerCase().includes(q)
  )
})
</script>

<template>
  <!-- Historial de facturación -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Facturación</h1>
        <NeumorphicButton @click="mostrarModal = true">
          + Generar Factura
        </NeumorphicButton>
      </div>

      <!-- Modal para generar factura -->
      <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="mostrarModal = false">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg mx-4 shadow-2xl space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Generar Factura</h2>
            <button @click="mostrarModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
          </div>

          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Servicio</label>
              <select v-model="form.servicio_id" @change="seleccionarServicio()" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar servicio...</option>
                <option v-for="s in servicios" :key="s.id" :value="s.id">{{ s.folio }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Subtotal</label>
              <NeumorphicInput v-model="form.subtotal" type="number" step="0.01" placeholder="0.00" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">IVA</label>
              <NeumorphicInput v-model="form.iva" type="number" step="0.01" placeholder="0.00" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Total</label>
              <NeumorphicInput v-model="form.total" type="number" step="0.01" placeholder="0.00" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Correo de envío</label>
              <NeumorphicInput v-model="form.correo_envio_factura" type="email" placeholder="cliente@correo.com" />
              <p class="text-xs text-gray-400 mt-1">Si se especifica, la factura se enviará automáticamente por email</p>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton @click="generarFactura()" :loading="form.processing">Generar y Enviar</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="mostrarModal = false; form.reset()">Cancelar</NeumorphicButton>
          </div>
        </div>
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
        <DataTable :columns="columns" :data="filteredFacturas">
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
          <template #actions="{ row }">
            <div class="flex items-center gap-2">
              <button @click="router.visit(route('panel.facturacion.show', { id: row.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              </button>
              <button
                v-if="row.enviada"
              @click="enviarFactura(row.id)"
              class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]"
              title="Reenviar factura por email"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

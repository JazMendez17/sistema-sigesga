<script setup>
import { ref, computed } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import Badge from '@/Components/Badge.vue'
import Swal from 'sweetalert2'

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

const kmExcedentes = computed(() => {
  if (!servicioSeleccionado.value) return 0
  const kms = servicioSeleccionado.value.kms_termino || servicioSeleccionado.value.distancia_km || 0
  const incluidos = servicioSeleccionado.value.km_incluidos || 0
  return Math.max(0, kms - incluidos)
})

const costoKmExtra = computed(() => {
  return (kmExcedentes.value * (servicioSeleccionado.value?.costo_km || 0)).toFixed(2)
})

function seleccionarServicio() {
  const s = servicioSeleccionado.value
  if (!s) return
  form.cliente_id = s.cliente_id || ''
  form.correo_envio_factura = s.cliente_email || ''
  
  // Calcular subtotal: banderazo + excedente de km
  let banderazo = s.costo_banderazo || 0
  let kmExtra = 0
  if (s.kms_termino && s.km_incluidos) {
    const excedente = Math.max(0, s.kms_termino - s.km_incluidos)
    kmExtra = excedente * (s.costo_km || 0)
  }
  const subtotalCalc = banderazo + kmExtra
  form.subtotal = subtotalCalc.toFixed(2)
  form.iva = (subtotalCalc * 0.16).toFixed(2)
  form.total = (subtotalCalc * 1.16).toFixed(2)
}

function generarFactura() {
  form.post(route('panel.facturacion.store'), {
    onSuccess: () => {
      mostrarModal.value = false
      form.reset()
      Swal.fire('Generada', 'Factura creada y enviada por correo.', 'success')
    },
  })
}

function enviarFactura(id) {
  Swal.fire({
    title: '¿Enviar factura?',
    text: 'Se enviará la factura en PDF al correo del cliente.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Enviar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#4F46E5',
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('panel.facturacion.enviar', { id }), {
        onSuccess: () => Swal.fire('Enviada', 'Factura enviada por correo.', 'success')
      })
    }
  })
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
        <div class="bg-white rounded-3xl w-full max-w-lg mx-4 shadow-2xl flex flex-col max-h-[92vh]">
          <!-- Encabezado fijo -->
          <div class="flex items-center justify-between p-6 pb-0">
            <h2 class="text-lg font-semibold text-gray-800">Generar Factura</h2>
            <button @click="mostrarModal = false; form.reset()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
          </div>

          <!-- Cuerpo con scroll -->
          <div class="overflow-y-auto px-6 py-4 space-y-4 flex-1">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Servicio</label>
              <select v-model="form.servicio_id" @change="seleccionarServicio()" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar servicio...</option>
                <option v-for="s in servicios" :key="s.id" :value="s.id">{{ s.folio }} - {{ s.cliente_nombre }}</option>
              </select>
            </div>

            <!-- Prefactura -->
            <div v-if="servicioSeleccionado" class="rounded-xl bg-gray-50 p-4 text-sm space-y-2 border">
              <p class="font-medium text-gray-700 text-center border-b pb-2">Prefactura - {{ servicioSeleccionado.folio }}</p>
              <div class="flex justify-between"><span class="text-gray-500">Cliente:</span><span class="font-medium">{{ servicioSeleccionado.cliente_nombre }}</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Tipo de Servicio:</span><span>{{ servicioSeleccionado.tipo_servicio }}</span></div>
              <div v-if="servicioSeleccionado.aseguradora && servicioSeleccionado.aseguradora !== 'Particular'" class="flex justify-between"><span class="text-gray-500">Aseguradora:</span><span>{{ servicioSeleccionado.aseguradora }}</span></div>
              <div v-if="servicioSeleccionado.numero_poliza && servicioSeleccionado.numero_poliza !== '—'" class="flex justify-between"><span class="text-gray-500">Póliza:</span><span>{{ servicioSeleccionado.numero_poliza }}</span></div>
              <div v-if="servicioSeleccionado.tipo_cobertura && servicioSeleccionado.tipo_cobertura !== '—'" class="flex justify-between"><span class="text-gray-500">Cobertura:</span><span>{{ servicioSeleccionado.tipo_cobertura }}</span></div>
              <div class="border-t pt-1">
                <div class="flex justify-between"><span class="text-gray-500">Banderazo Base:</span><span>${{ servicioSeleccionado.costo_banderazo?.toFixed(2) || '0.00' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">KM Incluidos:</span><span>{{ servicioSeleccionado.km_incluidos || 0 }} km</span></div>
                <div class="flex justify-between"><span class="text-gray-500">KM Recorridos:</span><span>{{ servicioSeleccionado.kms_termino || servicioSeleccionado.distancia_km || 0 }} km</span></div>
                <div class="flex justify-between"><span class="text-gray-500">KM Excedentes:</span><span class="text-orange-600">{{ kmExcedentes }} km × ${{ servicioSeleccionado.costo_km?.toFixed(2) || '0.00' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Costo KM Extra:</span><span>${{ costoKmExtra }}</span></div>
              </div>
              <div class="border-t pt-1 font-semibold">
                <div class="flex justify-between"><span>Subtotal:</span><span>${{ form.subtotal }}</span></div>
                <div class="flex justify-between"><span>IVA (16%):</span><span>${{ form.iva }}</span></div>
                <div class="flex justify-between text-base"><span>TOTAL:</span><span>${{ form.total }}</span></div>
              </div>
            </div>

            <NeumorphicInput v-model="form.subtotal" label="Subtotal ($)" type="number" step="0.01" placeholder="0.00" />
            <NeumorphicInput v-model="form.iva" label="IVA 16% ($)" type="number" step="0.01" placeholder="0.00" />
            <NeumorphicInput v-model="form.total" label="Total ($)" type="number" step="0.01" placeholder="0.00" />
            <NeumorphicInput v-model="form.correo_envio_factura" label="Correo de envío" type="email" placeholder="cliente@correo.com" />
            <p class="text-xs text-gray-400">La factura se enviará automáticamente por email al guardar</p>
          </div>

          <!-- Footer fijo -->
          <div class="flex gap-3 p-6 pt-0">
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

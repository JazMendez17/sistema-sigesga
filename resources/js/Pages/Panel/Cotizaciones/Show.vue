<script setup>
import { ref, computed } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const props = defineProps({
  cotizacion: Object,
})

const page = usePage()
const operadores = computed(() => page.props.operadores ?? [])
const unidades = computed(() => page.props.unidades ?? [])
const mostrarModal = ref(false)

const form = useForm({
  operador_id: '',
  unidad_id: '',
})

function aprobar() {
  mostrarModal.value = true
}

function confirmarAprobar() {
  form.post(route('panel.cotizaciones.aprobar', { id: props.cotizacion.id }), {
    onSuccess: () => { mostrarModal.value = false; form.reset() },
  })
}

function rechazar() {
  if (confirm('¿Rechazar esta cotización?')) {
    form.post(route('panel.cotizaciones.rechazar', { id: props.cotizacion.id }))
  }
}

function aprobarCliente() {
  if (confirm('¿Deseas aprobar esta cotización?')) {
    router.post(route('panel.cotizaciones.aprobar.cliente', { id: props.cotizacion.id }))
  }
}

function formato(val) { return val || '—' }
function moneda(val) { return Number(val || 0).toFixed(2) }
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ formato(cotizacion?.folio) }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle de cotización</p>
        </div>
        <NeumorphicButton @click="router.visit(route('panel.cotizaciones.index'))">Volver</NeumorphicButton>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="neumorphic-card p-6 lg:col-span-2 space-y-5">
          <div class="grid grid-cols-2 gap-4">
            <div><p class="text-xs text-gray-500 uppercase">Cliente</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.cliente) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Fecha</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.fecha) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Tipo de Servicio</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.tipo) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Estatus</p><Badge :variant="cotizacion?.estatus === 'aprobado' ? 'success' : cotizacion?.estatus === 'rechazado' ? 'danger' : 'warning'">{{ cotizacion?.estatus || 'pendiente' }}</Badge></div>
            <div class="col-span-2"><p class="text-xs text-gray-500 uppercase">Origen</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.origen) }}</p></div>
            <div class="col-span-2"><p class="text-xs text-gray-500 uppercase">Destino</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.destino) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Distancia</p><p class="text-gray-800 font-medium">{{ cotizacion?.distancia ? cotizacion.distancia + ' km' : '—' }}</p></div>
              <div><p class="text-xs text-gray-500 uppercase">Banderazo</p><p class="text-gray-800 font-medium">${{ moneda(cotizacion?.banderazo) }}</p></div>
              <div><p class="text-xs text-gray-500 uppercase">Kilómetros excedentes</p><p class="text-gray-800 font-medium">{{ cotizacion?.km_excedente || 0 }} km × ${{ moneda(cotizacion?.costo_km) }}</p></div>
              <div><p class="text-xs text-gray-500 uppercase">Subtotal</p><p class="text-gray-800 font-medium">${{ moneda(cotizacion?.subtotal) }}</p></div>
              <div><p class="text-xs text-gray-500 uppercase">Descuento</p><p class="text-gray-800 font-medium">{{ cotizacion?.descuento_pct || 0 }}% (${{ moneda(cotizacion?.monto_descuento) }})</p></div>
              <div><p class="text-xs text-gray-500 uppercase">IVA</p><p class="text-gray-800 font-medium">${{ moneda(cotizacion?.iva) }}</p></div>
              <div><p class="text-xs text-gray-500 uppercase">Casetas</p><p class="text-gray-800 font-medium">${{ moneda(cotizacion?.casetas) }} <span class="text-xs">{{ cotizacion?.incluye_peajes ? '(incluidas)' : '(cargo adicional)' }}</span></p></div>
            <div><p class="text-xs text-gray-500 uppercase">Total Estimado</p><p class="text-xl font-bold text-[var(--color-primary)]">${{ cotizacion?.total_estimado?.toFixed(2) || '0.00' }}</p></div>
          </div>

          <!-- Botones de aprobación -->
          <div v-if="cotizacion?.estatus === 'pendiente' && cotizacion?.puede_aprobar_cliente" class="flex gap-3 pt-3 border-t border-gray-200">
            <template v-if="cotizacion?.puede_aprobar_cliente">
              <NeumorphicButton @click="aprobarCliente" class="!bg-green-600 !text-white">Aprobar Cotización</NeumorphicButton>
            </template>
          </div>
          <p v-if="cotizacion?.estatus === 'pendiente' && !cotizacion?.puede_aprobar_cliente && !cotizacion?.cliente_aprobada_at" class="text-sm text-amber-700 border-t border-gray-200 pt-3">Pendiente de aprobación por el cliente.</p>
          <p v-if="cotizacion?.cliente_aprobada_at && cotizacion?.estatus === 'pendiente' && cotizacion?.es_cliente" class="text-sm text-amber-700 border-t border-gray-200 pt-3">Tu cotización está en espera de la aprobación final.</p>
          <template v-if="cotizacion?.estatus === 'pendiente' && cotizacion?.cliente_aprobada_at && !cotizacion?.es_cliente">
            <p class="text-sm text-amber-700 border-t border-gray-200 pt-3">Cliente aprobado. Valida la aseguradora y realiza la aprobación interna para crear el servicio.</p>
            <div class="flex gap-3 pt-3">
              <NeumorphicButton @click="aprobar" class="!bg-green-600 !text-white">Aprobar</NeumorphicButton>
              <NeumorphicButton variant="danger" @click="rechazar">Rechazar</NeumorphicButton>
            </div>
          </template>
          <div v-if="cotizacion?.servicio_id" class="pt-3 border-t border-gray-200">
            <p class="text-sm text-green-700">Servicio #{{ cotizacion.servicio_id }} creado</p>
          </div>
        </div>
      </div>

      <!-- Modal de asignación -->
      <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="mostrarModal = false">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md mx-4 shadow-2xl space-y-4">
          <h2 class="text-lg font-semibold text-gray-800">Asignar Operador y Unidad</h2>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Operador</label>
            <select v-model="form.operador_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
              <option value="">Seleccionar operador...</option>
              <option v-for="o in operadores" :key="o.id" :value="o.id">{{ o.nombre }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Unidad</label>
            <select v-model="form.unidad_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
              <option value="">Seleccionar unidad...</option>
              <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.placas }} - {{ u.numero_economico }}</option>
            </select>
          </div>
          <div class="flex gap-3 pt-2">
            <NeumorphicButton @click="confirmarAprobar()" :loading="form.processing" :disabled="!form.operador_id || !form.unidad_id">Confirmar y Crear Servicio</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="mostrarModal = false">Cancelar</NeumorphicButton>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: var(--color-surface); border-radius: 24px; box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light); }
</style>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const props = defineProps({ servicio: Object })

const mostrarModal = ref(false)
const mostrarModalCancelacion = ref(false)
const form = useForm({
  estado: '',
  kms_termino_servicio: '',
  observaciones: '',
})

const formCancelacion = useForm({
  motivo_cancelacion: '',
  tipo_incidencia: 'otro',
})

function finalizar() {
  form.estado = 'finalizado'
  form.post(route('panel.servicios.avanzar', { id: props.servicio.id }), { onSuccess: () => mostrarModal.value = false })
}

function enviarCancelacion() {
  formCancelacion.post(route('panel.servicios.solicitar-cancelacion', { id: props.servicio.id }), { onSuccess: () => mostrarModalCancelacion.value = false })
}

const esActivo = computed(() => ['asignado','inicio_servicio','en_sitio_origen','salida_destino','en_destino'].includes(props.servicio?.estatus))

function avanzar(estado) {
  if (estado === 'finalizado') { mostrarModal.value = true; return }
  router.post(route('panel.servicios.avanzar', { id: props.servicio.id }), { estado })
}

const pasos = [
  { key: 'asignado', label: 'Asignado', icon: '📋' },
  { key: 'inicio_servicio', label: 'En Camino', icon: '🚀' },
  { key: 'en_sitio_origen', label: 'En Origen', icon: '📍' },
  { key: 'salida_destino', label: 'En Tránsito', icon: '🚚' },
  { key: 'en_destino', label: 'En Destino', icon: '🏁' },
  { key: 'finalizado', label: 'Finalizado', icon: '✅' },
]

const idxActual = computed(() => pasos.findIndex(p => p.key === props.servicio?.estatus))
const finalizado = computed(() => ['finalizado','cancelado'].includes(props.servicio?.estatus))
const pasoActual = computed(() => pasos.find(p => p.key === props.servicio?.estatus))

const botonFlujo = {
  asignado: { label: 'Iniciar Servicio', next: 'inicio_servicio' },
  inicio_servicio: { label: 'Llegué al Origen', next: 'en_sitio_origen' },
  en_sitio_origen: { label: 'Salir al Destino', next: 'salida_destino' },
  salida_destino: { label: 'Llegué al Destino', next: 'en_destino' },
  en_destino: { label: 'Finalizar Servicio', next: 'finalizado' },
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-800">{{ servicio?.folio || '—' }}</h1><p class="text-sm text-gray-500 mt-1">Detalle del servicio</p></div>
        <NeumorphicButton @click="router.visit(route('panel.servicios.index'))">Volver</NeumorphicButton>
      </div>

      <!-- Stepped Progress Bar -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="flex items-center justify-between">
          <template v-for="(p, i) in pasos" :key="p.key">
            <div class="flex flex-col items-center gap-1 flex-1">
              <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg transition-all duration-300"
                :class="i < idxActual ? 'bg-green-500 text-white shadow-lg' : i === idxActual ? 'bg-[var(--color-primary)] text-white shadow-lg scale-110' : 'bg-gray-300 text-gray-500'">
                {{ p.icon }}
              </div>
              <span class="text-xs font-medium text-center" :class="i <= idxActual ? 'text-gray-800' : 'text-gray-400'">{{ p.label }}</span>
            </div>
            <div v-if="i < pasos.length - 1" class="h-1 flex-1 rounded-full mx-1 transition-all duration-300"
              :class="i < idxActual ? 'bg-green-500' : i === idxActual ? 'bg-[var(--color-primary)]' : 'bg-gray-300'" />
          </template>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="neumorphic-card p-6 lg:col-span-2 space-y-5">
          <div class="grid grid-cols-2 gap-4">
            <div><p class="text-xs text-gray-500 uppercase">Cliente</p><p class="text-gray-800 font-medium">{{ servicio?.cliente || '—' }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Fecha</p><p class="text-gray-800 font-medium">{{ servicio?.fecha || '—' }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Operador</p><p class="text-gray-800 font-medium">{{ servicio?.operador || '—' }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Unidad</p><p class="text-gray-800 font-medium">{{ servicio?.unidad || '—' }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Tipo</p><p class="text-gray-800 font-medium">{{ servicio?.tipo || '—' }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Estatus</p><Badge :variant="finalizado && servicio?.estatus!=='cancelado' ? 'success' : servicio?.estatus==='cancelado' ? 'danger' : 'warning'">{{ pasoActual?.label || servicio?.estatus || '—' }}</Badge></div>
            <div class="col-span-2"><p class="text-xs text-gray-500 uppercase">Origen</p><p class="text-gray-800 font-medium">{{ servicio?.origen || '—' }}</p></div>
            <div class="col-span-2"><p class="text-xs text-gray-500 uppercase">Destino</p><p class="text-gray-800 font-medium">{{ servicio?.destino || '—' }}</p></div>
            <div class="col-span-2"><p class="text-xs text-gray-500 uppercase">Observaciones</p><p class="text-gray-600">{{ servicio?.observaciones || '—' }}</p></div>
          </div>

          <!-- Botón de acción -->
          <div v-if="!finalizado" class="border-t border-gray-200 pt-4 space-y-3">
            <button @click="avanzar(botonFlujo[servicio?.estatus]?.next)"
              v-if="esActivo"
              class="w-full py-4 px-6 text-white font-bold text-lg rounded-2xl transition-all duration-200 shadow-lg hover:scale-[1.02]"
              :style="{ backgroundColor: 'var(--color-primary)' }">
              {{ botonFlujo[servicio?.estatus]?.label || 'Actualizar' }}
            </button>
            <button @click="mostrarModalCancelacion = true"
              v-if="esActivo"
              class="w-full py-3 px-6 text-red-600 font-semibold rounded-2xl bg-[#EEF2F7] shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all duration-200 hover:text-red-700 border border-red-200">
              Solicitar Cancelación
            </button>
            <p v-if="servicio?.estatus === 'solicitud_cancelacion'" class="text-center text-amber-600 font-medium py-4">
              Cancelación pendiente de autorización
            </p>
          </div>
          <div v-else class="border-t border-gray-200 pt-4 text-center text-sm text-gray-500">
            Servicio completado
          </div>
        </div>

        <div class="space-y-4">
          <div class="neumorphic-card p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Bitácora de Tiempos</h3>
            <div class="space-y-2 text-sm">
              <p class="text-gray-500">Salida: <span class="text-gray-800">{{ servicio?.bitacora?.salida || '—' }}</span></p>
              <p class="text-gray-500">Llegada cliente: <span class="text-gray-800">{{ servicio?.bitacora?.llegada || '—' }}</span></p>
              <p class="text-gray-500">Término: <span class="text-gray-800">{{ servicio?.bitacora?.termino || '—' }}</span></p>
              <p class="text-gray-500">Regreso: <span class="text-gray-800">{{ servicio?.bitacora?.regreso || '—' }}</span></p>
            </div>
          </div>
          <div class="neumorphic-card p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Kilometraje</h3>
            <div class="space-y-2 text-sm">
              <p class="text-gray-500">Salida: <span class="text-gray-800">{{ formatKm(servicio?.kms_salida) || '—' }}</span></p>
              <p class="text-gray-500">Llegada: <span class="text-gray-800">{{ formatKm(servicio?.kms_llegada_cliente) || '—' }}</span></p>
              <p class="text-gray-500">Término: <span class="text-gray-800">{{ formatKm(servicio?.kms_termino_servicio) || '—' }}</span></p>
              <p class="text-gray-500">Regreso: <span class="text-gray-800">{{ formatKm(servicio?.kms_regreso_base) || '—' }}</span></p>
              <p class="text-gray-500">Cobrados: <span class="text-gray-800">{{ formatKm(servicio?.kms_cobrados_reales) || '—' }}</span></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Finalizar -->
    <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="mostrarModal = false">
      <div class="bg-white rounded-3xl p-6 w-full max-w-md mx-4 shadow-2xl space-y-4">
        <h2 class="text-lg font-semibold text-gray-800">Finalizar Servicio</h2>
        <NeumorphicInput v-model="form.kms_termino_servicio" label="Kilometraje Final" type="number" placeholder="0" />
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Observaciones</label>
          <textarea v-model="form.observaciones" rows="3" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none" placeholder="Detalles del servicio..."></textarea>
        </div>
        <div class="flex gap-3 pt-2">
          <NeumorphicButton @click="finalizar()" :loading="form.processing">Confirmar Finalización</NeumorphicButton>
          <NeumorphicButton variant="secondary" @click="mostrarModal = false">Cancelar</NeumorphicButton>
        </div>
      </div>
    </div>

    <!-- Modal Solicitar Cancelación -->
    <div v-if="mostrarModalCancelacion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="mostrarModalCancelacion = false">
      <div class="bg-white rounded-3xl p-6 w-full max-w-md mx-4 shadow-2xl space-y-4">
        <h2 class="text-lg font-semibold text-gray-800">Solicitar Cancelación</h2>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Incidencia</label>
          <select v-model="formCancelacion.tipo_incidencia" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="cliente_cancela">El cliente cancela</option>
            <option value="operador_siniestro">Siniestro del operador</option>
            <option value="falla_mecanica">Falla mecánica</option>
            <option value="unidad_ponchada">Unidad ponchada</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Motivo</label>
          <textarea v-model="formCancelacion.motivo_cancelacion" rows="3" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none" placeholder="Describe el motivo..."></textarea>
          <p v-if="formCancelacion.errors.motivo_cancelacion" class="mt-1 text-xs text-red-500">{{ formCancelacion.errors.motivo_cancelacion }}</p>
          <p v-if="formCancelacion.errors.tipo_incidencia" class="mt-1 text-xs text-red-500">{{ formCancelacion.errors.tipo_incidencia }}</p>
        </div>
        <div class="flex gap-3 pt-2">
          <NeumorphicButton variant="danger" @click="enviarCancelacion()" :loading="formCancelacion.processing">Enviar Solicitud</NeumorphicButton>
          <NeumorphicButton variant="secondary" @click="mostrarModalCancelacion = false">Cancelar</NeumorphicButton>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: #EEF2F7; border-radius: 24px; box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff; }
</style>

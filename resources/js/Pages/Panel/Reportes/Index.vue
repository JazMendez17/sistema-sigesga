<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import Badge from '@/Components/Badge.vue'

const page = usePage()

const formServicios = useForm({ fecha_inicio: '', fecha_fin: '' })
const formCostos = useForm({ fecha_inicio: '', fecha_fin: '' })
const formRendimiento = useForm({ operador: '' })
const formCalificaciones = useForm({ fecha_inicio: '', fecha_fin: '' })

const reporte = computed(() => page.props.flash?.reporte || null)

function formatMoney(v) {
  return '$' + Number(v || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const badgeEstatus = {
  asignado: 'info',
  inicio_servicio: 'info',
  en_sitio_origen: 'info',
  salida_destino: 'info',
  en_destino: 'info',
  finalizado: 'success',
  solicitud_cancelacion: 'warning',
  cancelado: 'danger',
}

function generarServicios() {
  formServicios.post(route('panel.reportes.servicios'), { preserveScroll: true })
}

function generarCostos() {
  formCostos.post(route('panel.reportes.costos'), { preserveScroll: true })
}

function generarRendimiento() {
  formRendimiento.post(route('panel.reportes.rendimiento'), { preserveScroll: true })
}

function generarCalificaciones() {
  formCalificaciones.post(route('panel.reportes.calificaciones'), { preserveScroll: true })
}
</script>

<template>
  <!-- Panel de generación de reportes -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Reportes</h1>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Servicios por Periodo</h3>
            <p class="text-sm text-gray-500 mt-1">Genera un reporte de todos los servicios realizados en un rango de fechas.</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <NeumorphicInput v-model="formServicios.fecha_inicio" type="date" label="Fecha Inicio" />
            <NeumorphicInput v-model="formServicios.fecha_fin" type="date" label="Fecha Fin" />
          </div>
          <NeumorphicButton @click="generarServicios" variant="secondary" class="w-full" :disabled="formServicios.processing">Generar</NeumorphicButton>
        </div>

        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Costos e Ingresos</h3>
            <p class="text-sm text-gray-500 mt-1">Reporte financiero con costos operativos e ingresos por servicio.</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <NeumorphicInput v-model="formCostos.fecha_inicio" type="date" label="Fecha Inicio" />
            <NeumorphicInput v-model="formCostos.fecha_fin" type="date" label="Fecha Fin" />
          </div>
          <NeumorphicButton @click="generarCostos" variant="secondary" class="w-full" :disabled="formCostos.processing">Generar</NeumorphicButton>
        </div>

        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Rendimiento por Operador</h3>
            <p class="text-sm text-gray-500 mt-1">Evalúa el desempeño de cada operador según servicios completados.</p>
          </div>
          <NeumorphicInput v-model="formRendimiento.operador" placeholder="Nombre del operador (opcional)" />
          <NeumorphicButton @click="generarRendimiento" variant="secondary" class="w-full" :disabled="formRendimiento.processing">Generar</NeumorphicButton>
        </div>

        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Calificaciones Promedio</h3>
            <p class="text-sm text-gray-500 mt-1">Promedio de calificaciones recibidas por servicio o período.</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <NeumorphicInput v-model="formCalificaciones.fecha_inicio" type="date" label="Fecha Inicio" />
            <NeumorphicInput v-model="formCalificaciones.fecha_fin" type="date" label="Fecha Fin" />
          </div>
          <NeumorphicButton @click="generarCalificaciones" variant="secondary" class="w-full" :disabled="formCalificaciones.processing">Generar</NeumorphicButton>
        </div>
      </div>

      <!-- Resultados de reportes -->
      <div v-if="reporte" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-800 capitalize">
            Resultados: {{ reporte.type }}
          </h3>
          <span v-if="reporte.data?.length && reporte.type !== 'costos'" class="text-sm text-gray-500">{{ reporte.data.length }} registro(s)</span>
        </div>

        <!-- Tabla: servicios -->
        <div v-if="reporte.type === 'servicios'" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 uppercase text-xs">
                <th class="py-2 pr-4">Folio</th>
                <th class="py-2 pr-4">Cliente</th>
                <th class="py-2 pr-4">Tipo</th>
                <th class="py-2 pr-4">Fecha</th>
                <th class="py-2 pr-4">Costo</th>
                <th class="py-2">Estatus</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(s, i) in reporte.data" :key="i" class="border-t border-gray-300/50">
                <td class="py-2 pr-4 font-medium">{{ s.folio }}</td>
                <td class="py-2 pr-4">{{ s.cliente }}</td>
                <td class="py-2 pr-4">{{ s.tipo }}</td>
                <td class="py-2 pr-4">{{ s.fecha }}</td>
                <td class="py-2 pr-4">{{ formatMoney(s.costo) }}</td>
                <td class="py-2"><Badge :variant="badgeEstatus[s.estatus] || 'neutral'">{{ s.estatus }}</Badge></td>
              </tr>
              <tr v-if="!reporte.data?.length"><td colspan="6" class="py-4 text-center text-gray-400">Sin servicios en el periodo.</td></tr>
            </tbody>
          </table>
        </div>

        <!-- KPIs: costos -->
        <div v-if="reporte.type === 'costos'" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="rounded-2xl bg-white/60 p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Servicios</p>
            <p class="text-xl font-bold text-gray-800">{{ reporte.data.total_servicios }}</p>
          </div>
          <div class="rounded-2xl bg-white/60 p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Ingresos</p>
            <p class="text-xl font-bold text-green-600">{{ formatMoney(reporte.data.total_ingresos) }}</p>
          </div>
          <div class="rounded-2xl bg-white/60 p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Costos</p>
            <p class="text-xl font-bold text-red-500">{{ formatMoney(reporte.data.total_costos) }}</p>
          </div>
          <div class="rounded-2xl bg-white/60 p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Margen</p>
            <p class="text-xl font-bold text-[var(--color-primary)]">{{ formatMoney(reporte.data.margen) }}</p>
          </div>
        </div>

        <!-- Tabla: rendimiento -->
        <div v-if="reporte.type === 'rendimiento'" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 uppercase text-xs">
                <th class="py-2 pr-4">Operador</th>
                <th class="py-2 pr-4">Servicios</th>
                <th class="py-2 pr-4">Completados</th>
                <th class="py-2">Calificación Prom.</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in reporte.data" :key="i" class="border-t border-gray-300/50">
                <td class="py-2 pr-4 font-medium">{{ r.operador }}</td>
                <td class="py-2 pr-4">{{ r.total_servicios }}</td>
                <td class="py-2 pr-4">{{ r.completados }}</td>
                <td class="py-2">
                  <span class="text-amber-500" v-if="r.calificacion_promedio > 0">★ {{ r.calificacion_promedio }}</span>
                  <span v-else class="text-gray-400">—</span>
                </td>
              </tr>
              <tr v-if="!reporte.data?.length"><td colspan="4" class="py-4 text-center text-gray-400">Sin datos de operadores.</td></tr>
            </tbody>
          </table>
        </div>

        <!-- Tabla: calificaciones -->
        <div v-if="reporte.type === 'calificaciones'" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 uppercase text-xs">
                <th class="py-2 pr-4">Cliente</th>
                <th class="py-2 pr-4">Estrellas</th>
                <th class="py-2 pr-4">Comentario</th>
                <th class="py-2">Fecha</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in reporte.data" :key="i" class="border-t border-gray-300/50">
                <td class="py-2 pr-4 font-medium">{{ c.cliente }}</td>
                <td class="py-2 pr-4 text-amber-500">{{ '★'.repeat(c.estrellas) }}{{ '☆'.repeat(5 - c.estrellas) }}</td>
                <td class="py-2 pr-4">{{ c.comentario }}</td>
                <td class="py-2">{{ c.fecha }}</td>
              </tr>
              <tr v-if="!reporte.data?.length"><td colspan="4" class="py-4 text-center text-gray-400">Sin calificaciones en el periodo.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
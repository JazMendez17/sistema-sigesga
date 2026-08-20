<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import KPICard from '@/Components/KPICard.vue'
import ProgressDonut from '@/Components/ProgressDonut.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  role: { type: String, default: 'admin' },
  kpis: { type: Array, default: () => [] },
  recentActivity: { type: Array, default: () => [] },
  registrosPorDia: { type: Array, default: () => [] },
  registrosPorSemana: { type: Array, default: () => [] },
  registrosPorMes: { type: Array, default: () => [] },
  facturasPorDia: { type: Array, default: () => [] },
  facturasPorSemana: { type: Array, default: () => [] },
  facturasPorMes: { type: Array, default: () => [] },
  demoData: { type: Boolean, default: false },
  eficiencia: { type: Number, default: 0 },
  resumenOperacion: { type: Object, default: () => ({ asignados: 0, enTransito: 0, finalizados: 0 }) },
  serviciosHoy: { type: Array, default: () => [] },
  disponible: { type: Boolean, default: true },
  tieneOperador: { type: Boolean, default: true },
  siguienteServicio: { type: Object, default: null },
  historialCliente: { type: Array, default: () => [] },
  cotizacionesCliente: { type: Array, default: () => [] },
  facturasCliente: { type: Array, default: () => [] },
})

const disponibleLocal = ref(props.disponible ?? true)

const periodo = ref('mes')
const periodos = {
  dia: { label: 'Día', info: 'Últimos 7 días' },
  semana: { label: 'Semana', info: 'Últimas 4 semanas' },
  mes: { label: 'Mes', info: 'Últimos 6 meses' },
}
const chartData = computed(() => ({
  dia: props.registrosPorDia,
  semana: props.registrosPorSemana,
  mes: props.registrosPorMes,
}[periodo.value]))
const totalPeriodo = computed(() => chartData.value.reduce((acc, bar) => acc + (bar.count || 0), 0))

const periodoFacturas = ref('mes')
const chartDataFacturas = computed(() => ({
  dia: props.facturasPorDia,
  semana: props.facturasPorSemana,
  mes: props.facturasPorMes,
}[periodoFacturas.value]))
const totalPeriodoFacturas = computed(() => chartDataFacturas.value.reduce((acc, bar) => acc + (bar.count || 0), 0))
const montoPeriodoFacturas = computed(() => chartDataFacturas.value.reduce((acc, bar) => acc + (bar.monto || 0), 0))

const sliceColors = [
  'var(--color-primary)',
  '#EC4899',
  '#F59E0B',
  '#10B981',
  'var(--color-secondary)',
  '#EF4444',
  '#0EA5E9',
]

function barGradient(i) {
  const color = sliceColors[i % sliceColors.length]
  return `linear-gradient(180deg, color-mix(in srgb, ${color} 85%, white), ${color})`
}

const facturasSize = 192
const facturasRadius = 84
const facturasStroke = 18
const facturasCircumference = 2 * Math.PI * facturasRadius

const facturasSlices = computed(() => {
  const bars = chartDataFacturas.value
  const total = totalPeriodoFacturas.value
  if (!bars.length || total === 0) return []
  let acc = 0
  return bars.map((bar, i) => {
    const len = (bar.count / total) * facturasCircumference
    const rotate = (acc / total) * 360
    acc += bar.count
    return { len, rotate, color: sliceColors[i % sliceColors.length] }
  })
})

function formatMoney(v) {
  return '$' + Number(v || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function toggleDisponibilidad() {
  disponibleLocal.value = !disponibleLocal.value
  router.put(route('panel.operadores.disponibilidad'), { disponible: disponibleLocal.value }, {
    preserveScroll: true,
    onError: () => { disponibleLocal.value = props.disponible ?? true },
  })
}

// Variantes de Badge según el estado real de cotizaciones y servicios
const badgeVariant = (status) => ({
  pendiente: 'warning',
  aprobado: 'success',
  aprobada: 'success',
  rechazado: 'danger',
  rechazada: 'danger',
  asignado: 'info',
  inicio_servicio: 'info',
  en_sitio_origen: 'info',
  salida_destino: 'info',
  en_destino: 'info',
  finalizado: 'success',
  solicitud_cancelacion: 'warning',
  cancelado: 'danger',
  vigente: 'success',
  cancelada: 'danger',
}[status] || 'neutral')
</script>

<template>
  <!-- Panel principal de dashboard con KPIs, gráficas y actividad reciente -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-text-muted)]">Resumen</p>
          <h1 class="mt-1 text-3xl font-bold tracking-tight text-[var(--color-text)]">Dashboard</h1>
        </div>
        <NeumorphicButton v-if="role === 'admin' || role === 'cotizador'" @click="router.visit(route('panel.cotizaciones.create'))">
          + Nueva Cotización
        </NeumorphicButton>
      </div>

      <template v-if="role === 'admin' || role === 'cotizador'">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
          <KPICard
            v-for="(kpi, index) in kpis"
            :key="index"
            :title="kpi.title"
            :value="kpi.value"
            :icon="kpi.icon"
            :color="kpi.color"
          />
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
          <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Tráfico</p>
                <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Cotizaciones Registradas</h3>
              </div>
              <div class="flex gap-1 rounded-full bg-[var(--color-bg)] p-1 shadow-[inset_2px_2px_6px_var(--neumorphic-dark),inset_-2px_-2px_6px_var(--neumorphic-light)]">
                <button
                  v-for="(opts, key) in periodos"
                  :key="key"
                  @click="periodo = key"
                  class="rounded-full px-3 py-1.5 text-xs font-semibold transition-all duration-200"
                  :class="periodo === key ? 'bg-[var(--color-primary)] text-white shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]' : 'text-[var(--color-text-muted)] hover:text-[var(--color-text)]'"
                >
                  {{ opts.label }}
                </button>
              </div>
            </div>
            <Transition name="chart-fade" mode="out-in">
              <div :key="periodo" class="flex h-56 items-end justify-between gap-3 rounded-[24px] bg-[var(--color-bg)] p-4 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]">
                <div v-for="(bar, i) in chartData" :key="i" class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                  <div class="flex w-full items-end justify-center" style="height: 176px">
                    <div
                      class="bar-grow relative flex w-full items-start justify-center rounded-t-[18px] rounded-b-[4px] pt-1"
                      :style="{ '--h': Math.max(bar.height, 4) + '%', animationDelay: i * 0.15 + 's', background: bar.count > 0 ? barGradient(i) : 'rgba(148,163,184,0.25)', boxShadow: bar.count > 0 ? '0 8px 16px -6px color-mix(in srgb, ' + sliceColors[i % sliceColors.length] + ' 50%, transparent)' : 'none' }"
                      :title="bar.sub + ' · ' + bar.count + ' registros'"
                    >
                      <span v-if="bar.count > 0" class="text-[11px] font-bold text-white drop-shadow-[0_1px_2px_rgba(0,0,0,0.35)]">{{ bar.count }}</span>
                    </div>
                  </div>
                  <div class="flex flex-col items-center">
                    <span class="text-xs font-medium text-[var(--color-text-muted)]">{{ bar.label }}</span>
                  </div>
                </div>
              </div>
            </Transition>
            <p class="mt-3 text-center text-xs font-medium text-[var(--color-text-muted)]">{{ periodos[periodo].info }} — <span class="font-bold text-[var(--color-text)]">{{ totalPeriodo }}</span> registros <span v-if="totalPeriodo === 0" class="text-[var(--color-text-muted)]">(aún no hay cotizaciones en este periodo)</span></p>
          </div>

          <!-- Facturación: facturas terminadas por periodo (admin y cotizador) -->
          <div v-if="role === 'admin' || role === 'cotizador'" class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Facturación</p>
                <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Facturas Terminadas</h3>
              </div>
              <div class="flex gap-1 rounded-full bg-[var(--color-bg)] p-1 shadow-[inset_2px_2px_6px_var(--neumorphic-dark),inset_-2px_-2px_6px_var(--neumorphic-light)]">
                  <button
                    v-for="(opts, key) in periodos"
                    :key="key"
                    @click="periodoFacturas = key"
                    class="rounded-full px-3 py-1.5 text-xs font-semibold transition-all duration-200"
                    :class="periodoFacturas === key ? 'bg-[var(--color-primary)] text-white shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]' : 'text-[var(--color-text-muted)] hover:text-[var(--color-text)]'"
                  >
                    {{ opts.label }}
                  </button>
                </div>
            </div>
            <Transition name="chart-fade" mode="out-in">
              <div :key="periodoFacturas" class="flex flex-col items-center justify-center gap-4 rounded-[24px] bg-[var(--color-bg)] p-4 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]">
                <div class="relative flex h-48 w-48 items-center justify-center rounded-full shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)]">
                  <svg :width="facturasSize" :height="facturasSize" class="-rotate-90">
                    <circle v-if="totalPeriodoFacturas === 0" cx="96" cy="96" :r="facturasRadius" fill="none" stroke="rgba(148,163,184,0.25)" :stroke-width="facturasStroke" />
                    <circle
                      v-for="(slice, i) in facturasSlices"
                      :key="i"
                      cx="96"
                      cy="96"
                      :r="facturasRadius"
                      fill="none"
                      :stroke="slice.color"
                      :stroke-width="facturasStroke"
                      :transform="`rotate(${slice.rotate} 96 96)`"
                      class="facturas-slice"
                      :style="{ '--len': slice.len.toFixed(2) + 'px', '--circ': facturasCircumference.toFixed(2) + 'px', animationDelay: i * 0.18 + 's' }"
                    />
                  </svg>
                  <div class="absolute inset-7 rounded-full bg-[var(--color-bg)] shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]"></div>
                  <div class="absolute inset-0 z-10 flex items-center justify-center text-center">
                    <div>
                      <p class="text-3xl font-bold text-[var(--color-text)]">{{ totalPeriodoFacturas }}</p>
                      <p class="text-xs font-medium text-[var(--color-text-muted)]">facturas</p>
                    </div>
                  </div>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5">
                  <div v-for="(bar, i) in chartDataFacturas" :key="i" class="legend-item flex items-center gap-1.5" :style="{ animationDelay: i * 70 + 'ms' }">
                    <span class="legend-dot h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: sliceColors[i % sliceColors.length] }"></span>
                    <span class="text-xs text-[var(--color-text-muted)]">{{ bar.label }} · {{ bar.count }}</span>
                  </div>
                </div>
              </div>
            </Transition>
            <p class="mt-3 text-center text-xs font-medium text-[var(--color-text-muted)]">{{ periodos[periodoFacturas].info }} — <span class="font-bold text-[var(--color-text)]">{{ totalPeriodoFacturas }}</span> facturas <span v-if="montoPeriodoFacturas > 0" class="text-[var(--color-text)]">· {{ formatMoney(montoPeriodoFacturas) }}</span> <span v-if="totalPeriodoFacturas === 0" class="text-[var(--color-text-muted)]">(aún no hay facturas en este periodo)</span></p>
          </div>

          <!-- Rendimiento / Estadísticas (solo cotizador) -->
          <div v-if="role === 'cotizador'" class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Rendimiento</p>
              <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Estadísticas</h3>
            </div>
            <div class="flex flex-col items-center justify-center gap-4 rounded-[24px] bg-[var(--color-bg)] p-4 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]">
              <ProgressDonut :percentage="eficiencia" :color="'var(--color-primary)'" :size="140" />
              <div class="text-center">
                <p v-if="eficiencia > 0" class="text-2xl font-bold text-[var(--color-text)]">{{ eficiencia }}%</p>
                <p v-else class="text-lg font-semibold text-[var(--color-text-muted)]">Sin servicios finalizados</p>
                <p class="text-sm text-[var(--color-text-muted)]">Eficiencia General</p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.5fr_1fr]">
          <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Operación</p>
                <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Actividad Reciente</h3>
              </div>
            </div>
            <div class="space-y-3">
              <div
                v-for="item in recentActivity"
                :key="item.key || item.type + '-' + item.id"
                @click="router.visit(route(item.type === 'cotizacion' ? 'panel.cotizaciones.show' : 'panel.servicios.show', { id: item.id }))"
                class="flex cursor-pointer items-center justify-between gap-3 rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)] transition-transform duration-200 hover:translate-x-0.5"
              >
                <div class="flex items-center gap-4">
                  <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--color-surface)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]">
                    <svg v-if="item.type === 'cotizacion'" class="h-5 w-5 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <svg v-else class="h-5 w-5 text-[#059669]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1 2 1 2-1 2 1 2-1 2 1z" /></svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-[var(--color-text)]">{{ item.title }}</p>
                    <p class="text-xs text-[var(--color-text-muted)]">{{ item.description }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <Badge :variant="badgeVariant(item.status)">{{ item.status }}</Badge>
                  <span class="text-xs text-[var(--color-text-muted)]">{{ item.time }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Resumen</p>
              <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Operación</h3>
            </div>
            <div class="space-y-3">
              <div class="rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
                <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Asignados</p>
                <p class="mt-2 min-h-[2rem] text-2xl font-bold text-[var(--color-text)]">{{ resumenOperacion.asignados }}</p>
              </div>
              <div class="rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
                <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-text-muted)]">En tránsito</p>
                <p class="mt-2 min-h-[2rem] text-2xl font-bold text-[var(--color-text)]">{{ resumenOperacion.enTransito }}</p>
              </div>
              <div class="rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
                <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Finalizados</p>
                <p class="mt-2 min-h-[2rem] text-2xl font-bold text-[var(--color-text)]">{{ resumenOperacion.finalizados }}</p>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template v-if="role === 'operador'">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.7fr_1fr]">
          <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Operación</p>
                <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Mis Servicios Hoy</h3>
              </div>
            </div>
            <div class="space-y-3">
              <div
                v-for="sv in serviciosHoy"
                :key="sv.id"
                class="flex items-center justify-between gap-3 rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]"
              >
                <div class="flex items-center gap-4">
                  <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--color-surface)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]">
                    <svg class="h-5 w-5 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-[var(--color-text)]">{{ sv.cliente }}</p>
                    <p class="text-xs text-[var(--color-text-muted)]">{{ sv.ruta }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-xs text-[var(--color-text-muted)]">{{ sv.horario }}</span>
                  <Badge :variant="badgeVariant(sv.status)">{{ sv.status }}</Badge>
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-6">
            <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
              <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Siguiente</p>
                <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Servicio</h3>
              </div>
              <div class="rounded-[24px] bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-secondary)] p-5 text-white shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)]">
                <template v-if="siguienteServicio">
                  <p class="text-sm font-medium opacity-80">{{ siguienteServicio.folio }}</p>
                  <p class="mt-2 text-xl font-bold">{{ siguienteServicio.destino }}</p>
                  <p class="mt-2 text-sm opacity-80">Cliente: {{ siguienteServicio.cliente }}</p>
                  <p class="text-sm opacity-80">Inicio: {{ siguienteServicio.inicio }}</p>
                </template>
                <template v-else>
                  <p class="text-sm font-medium opacity-80">Próximo</p>
                  <p class="mt-2 text-xl font-bold">Sin servicios pendientes</p>
                  <p class="mt-2 text-sm opacity-80">No tienes servicios asignados por el momento.</p>
                </template>
              </div>
            </div>

            <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
              <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Estado</p>
                <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Disponibilidad</h3>
              </div>
              <button
                @click="toggleDisponibilidad"
                class="flex w-full items-center justify-between rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)] transition-all duration-300"
              >
                <div class="flex items-center gap-3">
                  <div class="h-4 w-4 rounded-full transition-colors duration-300" :class="disponibleLocal ? 'bg-green-500 shadow-[0_0_12px_rgba(34,197,94,0.5)]' : 'bg-red-500 shadow-[0_0_12px_rgba(239,68,68,0.5)]'"></div>
                  <span class="text-sm font-semibold text-[var(--color-text)]">{{ disponibleLocal ? 'Disponible' : 'No Disponible' }}</span>
                </div>
                <span class="text-xs text-[var(--color-text-muted)]">{{ disponibleLocal ? 'Tocando para desactivar' : 'Tocando para activar' }}</span>
              </button>
              <p v-if="tieneOperador === false" class="text-xs text-[var(--color-text-muted)] mt-2">No tienes un perfil de operador asignado. Contacta al administrador para activar tu disponibilidad.</p>
            </div>
          </div>
        </div>
      </template>

      <template v-if="role === 'cliente'">
        <div class="space-y-6">
          <!-- Mi Solicitud Activa - Tarjeta destacada -->
          <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_1.4fr]">
            <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
              <div class="mb-5"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Servicio</p><h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Mi Solicitud Activa</h3></div>
              <div v-if="historialCliente?.[0] && historialCliente[0].status !== 'finalizado' && historialCliente[0].status !== 'cancelado'" class="rounded-[24px] bg-gradient-to-br from-[#D97706] to-[#F59E0B] p-5 text-white">
                <div class="flex items-center justify-between gap-3"><span class="text-sm font-medium opacity-80">SVC-{{ String(historialCliente[0].id).padStart(5, '0') }}</span><Badge :variant="'warning'">{{ historialCliente[0].status }}</Badge></div>
                <p class="mt-3 text-xl font-bold">{{ historialCliente[0].servicio }}</p>
                <p class="mt-2 text-sm opacity-80">Fecha: {{ historialCliente[0].fecha }}</p>
                <p class="mt-2 text-sm opacity-80">Monto: {{ historialCliente[0].monto }}</p>
                <button @click="router.visit(route('panel.servicios.show.cliente', { id: historialCliente[0].id }))" class="mt-3 inline-flex items-center gap-1 bg-white text-[#D97706] font-semibold text-sm px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  Ver Seguimiento
                </button>
              </div>
              <div v-else class="rounded-[24px] bg-gradient-to-br from-gray-400 to-gray-500 p-6 text-white text-center"><svg class="w-12 h-12 mx-auto mb-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg><p class="text-lg font-bold">Sin servicios activos</p></div>
            </div>

            <!-- Historial Reciente - Tabla -->
            <div class="rounded-3xl bg-[#EEF2F7] p-5 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
              <h3 class="text-lg font-semibold text-gray-700 mb-4">Historial Reciente</h3>
              <div v-if="historialCliente?.length" class="overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="border-b border-gray-200"><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Folio</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Servicio</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Fecha</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Monto</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Estatus</th><th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Acción</th></tr></thead>
                <tbody><tr v-for="s in historialCliente" :key="s.id" class="border-b border-gray-100 hover:bg-white/50"><td class="py-3 px-3 font-medium">SVC-{{ String(s.id).padStart(5, '0') }}</td><td class="py-3 px-3">{{ s.servicio }}</td><td class="py-3 px-3 text-gray-500">{{ s.fecha }}</td><td class="py-3 px-3">{{ s.monto }}</td><td class="py-3 px-3"><Badge :variant="s.status === 'finalizado' ? 'success' : s.status === 'cancelado' ? 'danger' : 'warning'">{{ s.status }}</Badge></td><td class="py-3 px-3 text-right"><button @click="router.visit(route('panel.servicios.show.cliente', { id: s.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg></button></td></tr></tbody></table>
              </div>
              <p v-else class="text-sm text-gray-400 text-center py-4">Sin historial</p>
            </div>
          </div>

          <!-- Mis Cotizaciones -->
          <div class="rounded-3xl bg-[#EEF2F7] p-5 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Mis Cotizaciones</h3>
            <div v-if="cotizacionesCliente?.length" class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200"><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Folio</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Tipo</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Total</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Estatus</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Fecha</th><th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Acción</th></tr></thead>
                <tbody>
                  <tr v-for="c in cotizacionesCliente" :key="c.id" class="border-b border-gray-100 hover:bg-white/50">
                    <td class="py-3 px-3 font-medium">{{ c.folio }}</td>
                    <td class="py-3 px-3">{{ c.tipo }}</td>
                    <td class="py-3 px-3">{{ c.total }}</td>
                    <td class="py-3 px-3"><Badge :variant="c.estatus === 'aprobado' ? 'success' : c.estatus === 'rechazado' ? 'danger' : 'warning'">{{ c.estatus }}</Badge></td>
                    <td class="py-3 px-3 text-gray-500">{{ c.fecha }}</td>
                    <td class="py-3 px-3 text-right">
                      <button @click="router.visit(route('panel.cotizaciones.show.cliente', { id: c.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="text-sm text-gray-400 text-center py-4">Sin cotizaciones</p>
          </div>

          <!-- Mis Facturas -->
          <div class="rounded-3xl bg-[#EEF2F7] p-5 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Mis Facturas</h3>
            <div v-if="facturasCliente?.length" class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200"><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Folio</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Total</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Estatus</th><th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Fecha</th><th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Acción</th></tr></thead>
                <tbody>
                  <tr v-for="f in facturasCliente" :key="f.id" class="border-b border-gray-100 hover:bg-white/50">
                    <td class="py-3 px-3 font-medium">{{ f.folio }}</td>
                    <td class="py-3 px-3">{{ f.total }}</td>
                    <td class="py-3 px-3"><Badge :variant="f.estatus === 'vigente' ? 'success' : 'danger'">{{ f.estatus }}</Badge></td>
                    <td class="py-3 px-3 text-gray-500">{{ f.fecha }}</td>
                    <td class="py-3 px-3 text-right">
                      <button @click="router.visit(route('panel.facturacion.show.cliente', { id: f.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="text-sm text-gray-400 text-center py-4">Sin facturas</p>
          </div>
        </div>
      </template>
    </div>
  </AppLayout>
</template>

<style>
.chart-fade-enter-active,
.chart-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.chart-fade-enter-from {
  opacity: 0;
  transform: scale(0.9) translateY(6px);
}
.chart-fade-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-6px);
}
.legend-item {
  opacity: 0;
  animation: legend-in 0.4s ease forwards;
}
.legend-dot {
  animation: dot-pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes legend-in {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes dot-pop {
  from { transform: scale(0); }
  to { transform: scale(1); }
}
.facturas-slice {
  stroke-dasharray: 0 var(--circ);
  animation: fill-slice 0.7s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
@keyframes fill-slice {
  to { stroke-dasharray: var(--len) var(--circ); }
}
.bar-grow {
  height: 0%;
  animation: bar-grow 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
@keyframes bar-grow {
  to { height: var(--h); }
}
</style>

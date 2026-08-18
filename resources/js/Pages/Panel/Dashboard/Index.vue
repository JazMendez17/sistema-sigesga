<script setup>
import { ref } from 'vue'
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
  serviciosPorDia: { type: Array, default: () => [] },
  ventasPorSemana: { type: Array, default: () => [] },
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

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.7fr_1fr]">
          <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Tráfico</p>
                <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Facturas por Semana</h3>
              </div>
              <div class="rounded-full bg-[var(--color-bg)] px-3 py-1 text-xs font-medium text-[var(--color-text-muted)] shadow-[inset_2px_2px_6px_var(--neumorphic-dark),inset_-2px_-2px_6px_var(--neumorphic-light)]">
                Mes actual · 4 semanas
              </div>
            </div>
            <div class="flex h-56 items-end justify-between gap-3 rounded-[24px] bg-[var(--color-bg)] p-4 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]">
              <div v-for="(bar, i) in ventasPorSemana || serviciosPorDia" :key="i" class="flex flex-1 flex-col items-center gap-2">
                <div class="flex h-full w-full items-end">
                  <div class="w-full rounded-t-[18px] transition-all duration-500 ease-out" :style="{ height: Math.max(bar.height, 2) + '%', background: bar.count > 0 ? 'linear-gradient(180deg, var(--color-primary), var(--color-secondary))' : '#cbd5e1' }"></div>
                </div>
                <span class="text-xs font-medium text-[var(--color-text-muted)]">{{ bar.day }}</span>
                <span class="text-[10px] text-[var(--color-text-muted)]">{{ bar.count }}</span>
              </div>
            </div>
          </div>

          <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Rendimiento</p>
              <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Estadísticas</h3>
            </div>
            <div class="flex flex-col items-center justify-center gap-4 rounded-[24px] bg-[var(--color-bg)] p-4 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]">
              <ProgressDonut :percentage="eficiencia" :color="'var(--color-primary)'" :size="140" />
              <div class="text-center">
                <p class="text-2xl font-bold text-[var(--color-text)]">{{ eficiencia }}%</p>
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

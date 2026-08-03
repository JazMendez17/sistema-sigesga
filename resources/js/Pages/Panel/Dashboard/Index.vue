<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import KPICard from '@/Components/KPICard.vue'
import ProgressDonut from '@/Components/ProgressDonut.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

defineProps({
  role: { type: String, default: 'admin' },
  kpis: { type: Array, default: () => [] },
  recentActivity: { type: Array, default: () => [] },
  serviciosHoy: { type: Array, default: () => [] },
  historialCliente: { type: Array, default: () => [] },
})

const disponible = ref(true)

const statusColors = {
  pendiente: 'bg-yellow-100 text-yellow-800',
  aprobada: 'bg-green-100 text-green-800',
  rechazada: 'bg-red-100 text-red-800',
  en_curso: 'bg-blue-100 text-blue-800',
  finalizado: 'bg-green-100 text-green-800',
  asignado: 'bg-[var(--color-primary-light)] text-[var(--color-primary)]',
  cancelado: 'bg-gray-100 text-gray-800',
}
</script>

<template>
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
                <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Servicios por Día</h3>
              </div>
              <div class="rounded-full bg-[var(--color-bg)] px-3 py-1 text-xs font-medium text-[var(--color-text-muted)] shadow-[inset_2px_2px_6px_var(--neumorphic-dark),inset_-2px_-2px_6px_var(--neumorphic-light)]">
                Últimos 7 días
              </div>
            </div>
            <div class="flex h-56 items-end justify-between gap-3 rounded-[24px] bg-[var(--color-bg)] p-4 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]">
              <div v-for="(bar, i) in [{day:'Lun',h:60},{day:'Mar',h:45},{day:'Mié',h:80},{day:'Jue',h:55},{day:'Vie',h:90},{day:'Sáb',h:40},{day:'Dom',h:20}]" :key="i" class="flex flex-1 flex-col items-center gap-2">
                <div class="flex h-full w-full items-end">
                  <div class="w-full rounded-t-[18px] transition-all duration-500 ease-out" :style="{ height: bar.h + '%', background: 'linear-gradient(180deg, var(--color-primary), var(--color-secondary))' }"></div>
                </div>
                <span class="text-xs font-medium text-[var(--color-text-muted)]">{{ bar.day }}</span>
              </div>
            </div>
          </div>

          <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Rendimiento</p>
              <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Estadísticas</h3>
            </div>
            <div class="flex flex-col items-center justify-center gap-4 rounded-[24px] bg-[var(--color-bg)] p-4 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]">
              <ProgressDonut :percentage="0" :color="'var(--color-primary)'" :size="140" />
              <div class="text-center">
                <p class="text-2xl font-bold text-[var(--color-text)]">0%</p>
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
                :key="item.id"
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
                  <Badge :variant="item.status">{{ item.status }}</Badge>
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
                <p class="mt-2 text-2xl font-bold text-[var(--color-text)]">28</p>
              </div>
              <div class="rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
                <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-text-muted)]">En tránsito</p>
                <p class="mt-2 text-2xl font-bold text-[var(--color-text)]">12</p>
              </div>
              <div class="rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
                <p class="text-xs uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Finalizados</p>
                <p class="mt-2 text-2xl font-bold text-[var(--color-text)]">96</p>
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
                  <Badge :variant="sv.status">{{ sv.status }}</Badge>
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
                <p class="text-sm font-medium opacity-80">Próximo destino</p>
                <p class="mt-2 text-xl font-bold">Zona Industrial</p>
                <p class="mt-2 text-sm opacity-80">Cliente: María García</p>
                <p class="text-sm opacity-80">Horario: 09:00 - 11:00</p>
                <div class="mt-4 flex items-center gap-2">
                  <div class="h-2 w-2 animate-pulse rounded-full bg-white"></div>
                  <span class="text-xs font-medium">Inicia en 30 min</span>
                </div>
              </div>
            </div>

            <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
              <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Estado</p>
                <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Disponibilidad</h3>
              </div>
              <button
                @click="disponible = !disponible; router.put(route('panel.operadores.update', { id: 'me' }), { disponible: disponible }, { preserveScroll: true })"
                class="flex w-full items-center justify-between rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)] transition-all duration-300"
              >
                <div class="flex items-center gap-3">
                  <div class="h-4 w-4 rounded-full transition-colors duration-300" :class="disponible ? 'bg-green-500 shadow-[0_0_12px_rgba(34,197,94,0.5)]' : 'bg-red-500 shadow-[0_0_12px_rgba(239,68,68,0.5)]'"></div>
                  <span class="text-sm font-semibold text-[var(--color-text)]">{{ disponible ? 'Disponible' : 'No Disponible' }}</span>
                </div>
                <span class="text-xs text-[var(--color-text-muted)]">{{ disponible ? 'Tocando para desactivar' : 'Tocando para activar' }}</span>
              </button>
            </div>
          </div>
        </div>
      </template>

      <template v-if="role === 'cliente'">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_1.4fr]">
          <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Servicio</p>
              <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Mi Solicitud Activa</h3>
            </div>
            <div class="rounded-[24px] bg-gradient-to-br from-[#D97706] to-[#F59E0B] p-5 text-white shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)]">
              <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-medium opacity-80">Servicio #0089</span>
                <Badge variant="en_curso">En Curso</Badge>
              </div>
              <p class="mt-3 text-xl font-bold">Transporte Local</p>
              <p class="mt-2 text-sm opacity-80">Origen: Av. Principal #123</p>
              <p class="text-sm opacity-80">Destino: Zona Industrial</p>
              <div class="mt-4 border-t border-white/20 pt-4 text-sm">
                <div class="flex justify-between gap-3">
                  <span class="opacity-80">Operador asignado:</span>
                  <span class="font-semibold">Roberto Méndez</span>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-[30px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] sm:p-6">
            <div class="mb-5">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-text-muted)]">Historial</p>
              <h3 class="mt-1 text-xl font-semibold text-[var(--color-text)]">Reciente</h3>
            </div>
            <div class="space-y-3">
              <div
                v-for="item in historialCliente"
                :key="item.id"
                class="flex items-center justify-between gap-3 rounded-[22px] bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]"
              >
                <div class="flex items-center gap-4">
                  <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--color-surface)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-[var(--color-text)]">{{ item.servicio }}</p>
                    <p class="text-xs text-[var(--color-text-muted)]">{{ item.fecha }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-4">
                  <span class="text-sm font-semibold text-[var(--color-text)]">{{ item.monto }}</span>
                  <Badge :variant="item.status">{{ item.status }}</Badge>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </AppLayout>
</template>

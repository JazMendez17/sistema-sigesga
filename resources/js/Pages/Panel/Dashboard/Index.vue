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
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <NeumorphicButton v-if="role === 'admin' || role === 'cotizador'" @click="router.visit(route('panel.cotizaciones.create'))">
          + Nueva Cotización
        </NeumorphicButton>
      </div>

      <!-- Admin / Cotizador View -->
      <template v-if="role === 'admin' || role === 'cotizador'">
        <!-- KPI Grid -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
          <KPICard
            v-for="(kpi, index) in kpis"
            :key="index"
            :title="kpi.title"
            :value="kpi.value"
            :icon="kpi.icon"
            :color="kpi.color"
          />
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] lg:col-span-2">
            <h3 class="mb-4 text-lg font-semibold text-gray-700">Servicios por Día</h3>
            <div class="flex items-end justify-between gap-2" style="height: 180px">
              <div v-for="(bar, i) in [{day:'Lun',h:60},{day:'Mar',h:45},{day:'Mié',h:80},{day:'Jue',h:55},{day:'Vie',h:90},{day:'Sáb',h:40},{day:'Dom',h:20}]" :key="i" class="flex flex-1 flex-col items-center gap-2">
                <div class="w-full rounded-lg transition-all duration-500" :style="{ height: bar.h + '%', background: 'linear-gradient(180deg, var(--color-primary), var(--color-secondary))' }"></div>
                <span class="text-xs font-medium text-gray-500">{{ bar.day }}</span>
              </div>
            </div>
          </div>

          <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
            <h3 class="mb-4 text-lg font-semibold text-gray-700">Estadísticas</h3>
            <div class="flex flex-col items-center justify-center" style="height: 180px">
              <ProgressDonut :percentage="73" :color="'var(--color-primary)'" :size="140" />
              <p class="mt-3 text-sm text-gray-500">Eficiencia General</p>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
          <h3 class="mb-4 text-lg font-semibold text-gray-700">Actividad Reciente</h3>
          <div class="space-y-3">
            <div
              v-for="item in recentActivity"
              :key="item.id"
              @click="router.visit(route(item.type === 'cotizacion' ? 'panel.cotizaciones.show' : 'panel.servicios.show', { id: item.id }))"
              class="flex cursor-pointer items-center justify-between rounded-2xl bg-[var(--color-bg)] p-4 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]"
            >
              <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-surface)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]">
                  <svg v-if="item.type === 'cotizacion'" class="h-5 w-5 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                  <svg v-else class="h-5 w-5 text-[#059669]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1 2 1 2-1 2 1 2-1 2 1z" /></svg>
                </div>
                <div>
                  <p class="text-sm font-semibold text-gray-800">{{ item.title }}</p>
                  <p class="text-xs text-gray-500">{{ item.description }}</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <Badge :variant="item.status">{{ item.status }}</Badge>
                <span class="text-xs text-gray-400">{{ item.time }}</span>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- Operador View -->
      <template v-if="role === 'operador'">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <!-- Mis Servicios Hoy -->
          <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] lg:col-span-2">
            <h3 class="mb-4 text-lg font-semibold text-gray-700">Mis Servicios Hoy</h3>
            <div class="space-y-3">
              <div
                v-for="sv in serviciosHoy"
                :key="sv.id"
                class="flex items-center justify-between rounded-2xl bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]"
              >
                <div class="flex items-center gap-4">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-surface)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]">
                    <svg class="h-5 w-5 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-gray-800">{{ sv.cliente }}</p>
                    <p class="text-xs text-gray-500">{{ sv.ruta }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-xs text-gray-500">{{ sv.horario }}</span>
                  <Badge :variant="sv.status">{{ sv.status }}</Badge>
                </div>
              </div>
            </div>
          </div>

          <!-- Siguiente Servicio + Disponibilidad -->
          <div class="space-y-6">
            <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
              <h3 class="mb-4 text-lg font-semibold text-gray-700">Siguiente Servicio</h3>
              <div class="rounded-2xl bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-secondary)] p-5 text-white shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
                <p class="text-sm font-medium opacity-80">Próximo destino</p>
                <p class="mt-1 text-lg font-bold">Zona Industrial</p>
                <p class="mt-2 text-sm opacity-80">Cliente: María García</p>
                <p class="text-sm opacity-80">Horario: 09:00 - 11:00</p>
                <div class="mt-4 flex items-center gap-2">
                  <div class="h-2 w-2 animate-pulse rounded-full bg-white"></div>
                  <span class="text-xs font-medium">Inicia en 30 min</span>
                </div>
              </div>
            </div>

            <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
              <h3 class="mb-4 text-lg font-semibold text-gray-700">Disponibilidad</h3>
              <button
                @click="disponible = !disponible"
                class="flex w-full items-center justify-between rounded-2xl p-4 transition-all duration-300"
                :class="disponible ? 'bg-[var(--color-bg)] shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]' : 'bg-[var(--color-bg)] shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]'"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="h-4 w-4 rounded-full transition-colors duration-300"
                    :class="disponible ? 'bg-green-500 shadow-[0_0_12px_rgba(34,197,94,0.5)]' : 'bg-red-500 shadow-[0_0_12px_rgba(239,68,68,0.5)]'"
                  ></div>
                  <span class="text-sm font-semibold text-gray-800">{{ disponible ? 'Disponible' : 'No Disponible' }}</span>
                </div>
                <span class="text-xs text-gray-500">{{ disponible ? 'Tocando para desactivar' : 'Tocando para activar' }}</span>
              </button>
            </div>
          </div>
        </div>
      </template>

      <!-- Cliente View -->
      <template v-if="role === 'cliente'">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <!-- Mi Solicitud Activa -->
          <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] lg:col-span-1">
            <h3 class="mb-4 text-lg font-semibold text-gray-700">Mi Solicitud Activa</h3>
            <div class="rounded-2xl bg-gradient-to-br from-[#D97706] to-[#F59E0B] p-5 text-white shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium opacity-80">Servicio #0089</span>
                <Badge variant="en_curso">En Curso</Badge>
              </div>
              <p class="mt-3 text-lg font-bold">Transporte Local</p>
              <p class="mt-1 text-sm opacity-80">Origen: Av. Principal #123</p>
              <p class="text-sm opacity-80">Destino: Zona Industrial</p>
              <div class="mt-4 border-t border-white/20 pt-4">
                <div class="flex justify-between text-sm">
                  <span class="opacity-80">Operador asignado:</span>
                  <span class="font-semibold">Roberto Méndez</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Historial Reciente -->
          <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] lg:col-span-2">
            <h3 class="mb-4 text-lg font-semibold text-gray-700">Historial Reciente</h3>
            <div class="space-y-3">
              <div
                v-for="item in historialCliente"
                :key="item.id"
                class="flex items-center justify-between rounded-2xl bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]"
              >
                <div class="flex items-center gap-4">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-surface)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-gray-800">{{ item.servicio }}</p>
                    <p class="text-xs text-gray-500">{{ item.fecha }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-4">
                  <span class="text-sm font-semibold text-gray-700">{{ item.monto }}</span>
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

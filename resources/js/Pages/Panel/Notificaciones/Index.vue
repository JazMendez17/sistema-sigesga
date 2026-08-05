<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'

const filtroCanal = ref('todos')
const filtroEstado = ref('todos')

const page = usePage()
const notificaciones = computed(() => page.props.notificaciones || [])

const notificacionesFiltradas = computed(() =>
  notificaciones.value.filter(n =>
    (filtroCanal.value === 'todos' || n.canal === filtroCanal.value) &&
    (filtroEstado.value === 'todos' || n.estado === filtroEstado.value)
  )
)

const columns = [
  { key: 'usuario', label: 'Usuario' },
  { key: 'mensaje', label: 'Mensaje' },
  { key: 'canal', label: 'Canal' },
  { key: 'estado', label: 'Estado' },
  { key: 'intentos', label: 'Intentos' },
  { key: 'fecha', label: 'Fecha' },
]

const canales = ['todos', 'whatsapp', 'email', 'sms']
const estados = ['todos', 'enviado', 'fallido']

function reenviar(id) {
  router.post(route('panel.notificaciones.reenviar', { id }))
}
</script>

<template>
  <!-- Historial de notificaciones enviadas con filtros por canal y estado -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Notificaciones</h1>
      </div>

      <div class="flex flex-wrap gap-4">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="c in canales"
            :key="c"
            @click="filtroCanal = c"
            class="rounded-xl px-5 py-2 text-sm font-medium capitalize transition-all duration-200"
            :class="filtroCanal === c
              ? 'bg-[var(--color-surface)] text-[var(--color-primary)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]'
              : 'bg-transparent text-gray-500 hover:text-gray-700'"
          >
            {{ c === 'todos' ? 'Todos' : c }}
          </button>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="e in estados"
            :key="e"
            @click="filtroEstado = e"
            class="rounded-xl px-5 py-2 text-sm font-medium capitalize transition-all duration-200"
            :class="filtroEstado === e
              ? 'bg-[var(--color-surface)] text-[var(--color-primary)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]'
              : 'bg-transparent text-gray-500 hover:text-gray-700'"
          >
            {{ e === 'todos' ? 'Todos' : e }}
          </button>
        </div>
      </div>

      <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
        <DataTable :columns="columns" :data="notificacionesFiltradas">
          <template #cell-mensaje="{ row }">
            <span class="block max-w-xs truncate" :title="row.mensaje">{{ row.mensaje }}</span>
          </template>
          <template #cell-canal="{ row }">
            <Badge :variant="row.canal === 'whatsapp' ? 'success' : row.canal === 'email' ? 'info' : 'neutral'">{{ row.canal }}</Badge>
          </template>
          <template #cell-estado="{ row }">
            <Badge :variant="row.estado === 'enviado' ? 'success' : 'danger'">{{ row.estado }}</Badge>
          </template>
          <template #actions="{ row }">
            <div class="flex items-center gap-2">
              <button
                v-if="row.estado === 'fallido'"
                @click="reenviar(row.id)"
                class="rounded-lg bg-[var(--color-surface)] p-2 text-[var(--color-primary)] shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)] transition-all hover:text-[var(--color-primary)]"
                title="Reenviar"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

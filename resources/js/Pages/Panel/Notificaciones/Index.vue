<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Swal from 'sweetalert2'

const filtroActivo = ref('todos')
const filtros = ['todos', 'pendiente', 'leido']

const page = usePage()
const paginacion = computed(() => page.props.notificaciones || {})
const notificaciones = computed(() => paginacion.value?.data || (Array.isArray(paginacion.value) ? paginacion.value : []))
const links = computed(() => Array.isArray(paginacion.value?.links) ? paginacion.value.links : [])

function notificacionesFiltradas() {
  const data = Array.isArray(notificaciones.value) ? notificaciones.value : []
  if (filtroActivo.value === 'todos') return data
  return data.filter(n => n.estado === filtroActivo.value)
}

function marcarLeida(id) {
  router.post(route('panel.notificaciones.marcar-leida', { id }))
}

function marcarTodas() {
  router.post(route('panel.notificaciones.marcar-todas'))
}

function eliminar(id) {
  Swal.fire({
    title: '¿Eliminar notificación?',
    text: 'Se eliminará permanentemente del sistema.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Eliminar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#DC2626',
  }).then((res) => {
    if (res.isConfirmed) {
      router.delete(route('panel.notificaciones.eliminar', { id }))
    }
  })
}

function getBadgeVariant(estado) {
  if (estado === 'leido') return 'neutral'
  if (estado === 'fallido') return 'danger'
  return 'success'
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Notificaciones</h1>
          <p class="text-sm text-gray-500 mt-1">Eventos operativos de servicios y cotizaciones</p>
        </div>
        <NeumorphicButton type="button" @click="marcarTodas" :disabled="!(page.props.noLeidas > 0)">
          Marcar todas como leídas
        </NeumorphicButton>
      </div>

      <div class="flex flex-wrap gap-2">
        <button v-for="f in filtros" :key="f" @click="filtroActivo = f"
          class="rounded-xl px-5 py-2 text-sm font-medium capitalize transition-all duration-200"
          :class="filtroActivo === f ? 'bg-[#EEF2F7] text-[#4F46E5] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]' : 'bg-transparent text-gray-500 hover:text-gray-700'">
          {{ f === 'todos' ? 'Todas' : f === 'pendiente' ? 'No Leídas' : 'Leídas' }}
        </button>
      </div>

      <div class="rounded-3xl bg-[#EEF2F7] overflow-hidden shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <div class="divide-y divide-[#d0d5da]/20">
          <div v-if="notificacionesFiltradas().length === 0" class="px-6 py-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            <p class="text-sm">No hay notificaciones</p>
          </div>
          <div v-for="n in notificacionesFiltradas()" :key="n.id"
            class="px-6 py-4 hover:bg-white/30 transition-colors flex items-start gap-4"
            :class="{ 'bg-[var(--color-primary-light)]/10': n.estado !== 'leido' }">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
              :class="n.estado === 'leido' ? 'bg-gray-200' : 'bg-[var(--color-primary-light)]'">
              <svg class="w-5 h-5" :class="n.estado === 'leido' ? 'text-gray-400' : 'text-[var(--color-primary)]'"
                fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-medium text-gray-800">{{ n.mensaje }}</p>
                <Badge :variant="getBadgeVariant(n.estado)">{{ n.estado === 'leido' ? 'Leída' : $etiqueta(n.estado) }}</Badge>
              </div>
              <div class="flex items-center gap-3 mt-1.5">
                <span class="text-xs text-gray-400">{{ n.fecha }}</span>
                <button v-if="n.estado !== 'leido'" @click="marcarLeida(n.id)"
                  class="ml-auto text-xs text-[var(--color-primary)] hover:underline font-medium">
                  Marcar como leída
                </button>
                <button @click="eliminar(n.id)"
                  class="text-xs text-red-500 hover:underline font-medium"
                  :class="{ 'ml-auto': n.estado === 'leido' }">
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-2">
        <button
          v-for="(link, idx) in links"
          :key="idx"
          type="button"
          :disabled="!link.url"
          @click="link.url && router.visit(link.url, { preserveScroll: true })"
          class="rounded-xl px-3.5 py-1.5 text-sm font-medium transition-all"
          :class="link.active
            ? 'bg-[var(--color-primary)] text-white shadow'
            : link.url
              ? 'bg-[#EEF2F7] text-gray-700 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] hover:text-[var(--color-primary)]'
              : 'opacity-40 cursor-not-allowed text-gray-400'"
          v-html="link.label"
        />
      </div>
    </div>
  </AppLayout>
</template>

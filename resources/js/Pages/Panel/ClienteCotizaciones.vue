<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import Badge from '@/Components/Badge.vue'

defineProps({ cotizaciones: Array })
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold text-gray-800">Mis Cotizaciones</h1>
      <div v-if="cotizaciones?.length" class="rounded-3xl bg-[#EEF2F7] overflow-hidden shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <table class="w-full">
          <thead><tr class="border-b border-[#d0d5da]/30"><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Folio</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Origen</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Destino</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estatus</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th><th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acción</th></tr></thead>
          <tbody class="divide-y divide-[#d0d5da]/20">
            <tr v-for="c in cotizaciones" :key="c.id" class="hover:bg-white/30 text-sm">
              <td class="px-4 py-3">{{ c.folio }}</td><td class="px-4 py-3">{{ c.tipo }}</td><td class="px-4 py-3 max-w-[150px] truncate">{{ c.origen }}</td><td class="px-4 py-3 max-w-[150px] truncate">{{ c.destino }}</td><td class="px-4 py-3">{{ c.total }}</td>
              <td class="px-4 py-3"><Badge :variant="c.estatus === 'aprobado' ? 'success' : c.estatus === 'rechazado' ? 'danger' : 'warning'">{{ c.estatus }}</Badge></td>
              <td class="px-4 py-3">{{ c.fecha }}</td>
              <td class="px-4 py-3 text-right">
                <button @click="router.visit(route('panel.cotizaciones.show.cliente', { id: c.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="text-gray-400 text-center py-12">No tienes cotizaciones registradas.</p>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const page = usePage()
const pagos = computed(() => page.props.pagos || [])
const resumen = computed(() => page.props.resumen || { total_cobrado: 0, total_pagos: 0, cobrado_mes: 0 })

const busqueda = ref('')
const detalle = ref(null)

const pagosFiltrados = computed(() => {
  const q = busqueda.value.trim().toLowerCase()
  if (!q) return pagos.value
  return pagos.value.filter((p) =>
    [
      p.referencia,
      p.clave_rastreo,
      p.folio_cotizacion,
      p.cliente,
      p.titular,
      p.tarjeta_ultimos4,
      p.tarjeta_marca,
      p.pasarela,
    ]
      .filter(Boolean)
      .some((val) => String(val).toLowerCase().includes(q))
  )
})

function moneda(val) {
  return Number(val || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<template>
  <!-- Historial de pagos con tarjeta para superadmin, administrador y cotizador -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Pagos con Tarjeta</h1>
          <p class="text-sm text-gray-500 mt-1">Consulta cobros realizados, terminación de tarjeta, referencia y clave de rastreo.</p>
        </div>
      </div>

      <!-- KPIs -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="rounded-3xl bg-[#EEF2F7] p-5 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <p class="text-xs font-semibold uppercase text-gray-500">Total Cobrado</p>
          <p class="mt-2 text-2xl font-bold text-emerald-700">${{ moneda(resumen.total_cobrado) }} MXN</p>
        </div>
        <div class="rounded-3xl bg-[#EEF2F7] p-5 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <p class="text-xs font-semibold uppercase text-gray-500">Cobrado Este Mes</p>
          <p class="mt-2 text-2xl font-bold text-indigo-700">${{ moneda(resumen.cobrado_mes) }} MXN</p>
        </div>
        <div class="rounded-3xl bg-[#EEF2F7] p-5 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
          <p class="text-xs font-semibold uppercase text-gray-500">Pagos Aprobados</p>
          <p class="mt-2 text-2xl font-bold text-gray-800">{{ resumen.total_pagos }}</p>
        </div>
      </div>

      <!-- Buscador y tabla -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar por clave de rastreo, referencia, folio, cliente o terminación..."
            class="w-full sm:max-w-md rounded-2xl bg-[#E8EDF2] px-4 py-2.5 text-sm text-gray-700 shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300"
          />
          <span class="text-xs text-gray-500">{{ pagosFiltrados.length }} pago(s)</span>
        </div>

        <div v-if="pagosFiltrados.length" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-[#d0d5da]/40 text-left text-xs font-semibold uppercase text-gray-500">
                <th class="px-3 py-3">Fecha</th>
                <th class="px-3 py-3">Cotización</th>
                <th class="px-3 py-3">Cliente / Titular</th>
                <th class="px-3 py-3">Tarjeta</th>
                <th class="px-3 py-3">Monto</th>
                <th class="px-3 py-3">Referencia / Clave Rastreo</th>
                <th class="px-3 py-3">Pasarela</th>
                <th class="px-3 py-3">Estatus</th>
                <th class="px-3 py-3 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/30">
              <tr v-for="p in pagosFiltrados" :key="p.id" class="hover:bg-white/40">
                <td class="px-3 py-3 whitespace-nowrap text-gray-600">{{ p.pagado_en }}</td>
                <td class="px-3 py-3 whitespace-nowrap">
                  <button
                    type="button"
                    @click="router.visit(route('panel.cotizaciones.show', { id: p.cotizacion_id }))"
                    class="font-semibold text-indigo-600 hover:underline"
                  >
                    {{ p.folio_cotizacion }}
                  </button>
                </td>
                <td class="px-3 py-3">
                  <p class="font-medium text-gray-800">{{ p.cliente }}</p>
                  <p class="text-xs text-gray-500">Titular: {{ p.titular }}</p>
                </td>
                <td class="px-3 py-3 whitespace-nowrap">
                  <p class="font-mono font-semibold text-gray-800">{{ p.tarjeta_resumen }}</p>
                  <p class="text-xs text-gray-500">Vig: {{ p.tarjeta_vigencia }}</p>
                </td>
                <td class="px-3 py-3 whitespace-nowrap font-bold text-emerald-700">
                  ${{ moneda(p.monto) }} {{ p.moneda }}
                </td>
                <td class="px-3 py-3">
                  <p class="font-mono text-xs font-semibold text-gray-800">{{ p.referencia }}</p>
                  <p class="font-mono text-[11px] text-gray-500 break-all">Rastreo: {{ p.clave_rastreo }}</p>
                </td>
                <td class="px-3 py-3 whitespace-nowrap">
                  <Badge :variant="p.pasarela === 'Mercado Pago' ? 'info' : 'neutral'">{{ p.pasarela }}</Badge>
                </td>
                <td class="px-3 py-3 whitespace-nowrap">
                  <Badge :variant="p.estatus === 'pagado' ? 'success' : p.estatus === 'fallido' ? 'danger' : 'warning'">
                    {{ $etiqueta(p.estatus) }}
                  </Badge>
                </td>
                <td class="px-3 py-3 text-right whitespace-nowrap">
                  <button
                    type="button"
                    @click="detalle = p"
                    class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]"
                    title="Ver comprobante completo"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="py-12 text-center text-gray-400">Aún no hay pagos con tarjeta registrados.</p>
      </div>

      <!-- Modal de comprobante completo -->
      <div v-if="detalle" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="detalle = null">
        <div class="w-full max-w-md rounded-3xl bg-[#EEF2F7] p-6 shadow-2xl space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">Detalle de Pago</h2>
            <Badge :variant="detalle.estatus === 'pagado' ? 'success' : 'danger'">{{ $etiqueta(detalle.estatus) }}</Badge>
          </div>
          <div class="rounded-2xl bg-white/80 p-4 space-y-2 text-sm">
            <div class="flex justify-between gap-2"><span class="text-gray-500">Cotización:</span><strong class="text-gray-800">{{ detalle.folio_cotizacion }}</strong></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Cliente:</span><span class="font-medium text-gray-800">{{ detalle.cliente }}</span></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Titular tarjeta:</span><span class="font-medium text-gray-800">{{ detalle.titular }}</span></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Tarjeta:</span><strong class="font-mono text-gray-800">{{ detalle.tarjeta_resumen }}</strong></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Vigencia:</span><span class="text-gray-800">{{ detalle.tarjeta_vigencia }}</span></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Monto:</span><strong class="text-emerald-700">${{ moneda(detalle.monto) }} {{ detalle.moneda }}</strong></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Referencia interna:</span><strong class="font-mono text-xs text-gray-800">{{ detalle.referencia }}</strong></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Clave de rastreo:</span><strong class="font-mono text-xs text-gray-800 break-all text-right">{{ detalle.clave_rastreo }}</strong></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Pasarela:</span><span class="text-gray-800">{{ detalle.pasarela }}</span></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Registrado por:</span><span class="text-gray-800">{{ detalle.registrado_por }}</span></div>
            <div class="flex justify-between gap-2"><span class="text-gray-500">Fecha y hora:</span><span class="text-gray-800">{{ detalle.pagado_en }}</span></div>
          </div>
          <div class="flex gap-3">
            <NeumorphicButton type="button" class="flex-1" @click="router.visit(route('panel.cotizaciones.show', { id: detalle.cotizacion_id }))">Ver Cotización</NeumorphicButton>
            <NeumorphicButton type="button" variant="secondary" @click="detalle = null">Cerrar</NeumorphicButton>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

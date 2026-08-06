<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
  aseguradora: { type: Object, required: true },
})

function toggleContacto(contactoId, activo) {
  router.put(route('panel.aseguradoras.contacto.toggle', { id: props.aseguradora.id, contacto: contactoId }), {
    activo: !activo,
  })
}

function eliminarContacto(contactoId) {
  if (!confirm('¿Eliminar este contacto?')) return
  router.delete(route('panel.aseguradoras.contacto.destroy', { id: props.aseguradora.id, contacto: contactoId }))
}
</script>

<template>
  <!-- Detalle de aseguradora -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ aseguradora.nombre_comercial || aseguradora.nombre }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalles de la aseguradora</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.aseguradoras.index'))">
            Volver
          </NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.aseguradoras.edit', aseguradora.id))">
            Editar
          </NeumorphicButton>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] lg:col-span-2">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos Generales</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Nombre</p>
              <p class="text-sm font-medium text-gray-800">{{ aseguradora.nombre }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Nombre Comercial</p>
              <p class="text-sm font-medium text-gray-800">{{ aseguradora.nombre_comercial || '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">RFC</p>
              <p class="text-sm font-medium text-gray-800">{{ aseguradora.rfc || '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Teléfono</p>
              <p class="text-sm font-medium text-gray-800">{{ aseguradora.telefono || '—' }}</p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="aseguradora.contactos?.length" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Contactos</h3>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Departamento</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Teléfono</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Email</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Estado</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="cto in aseguradora.contactos" :key="cto.id" class="hover:bg-white/30 transition-colors" :class="{ 'opacity-50': !cto.activo }">
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ cto.departamento }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ cto.nombre_contacto }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ cto.telefono }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ cto.email || '—' }}</td>
                <td class="px-4 py-3">
                  <button @click="toggleContacto(cto.id, cto.activo)" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200" :class="cto.activo ? 'bg-[#059669]' : 'bg-gray-300'">
                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200" :class="cto.activo ? 'translate-x-5' : 'translate-x-0.5'" />
                  </button>
                </td>
                <td class="px-4 py-3 text-right">
                  <button @click="eliminarContacto(cto.id)" class="rounded-lg p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="aseguradora.convenios?.length" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Convenios</h3>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[#d0d5da]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Tipo Servicio</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Estatus</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#d0d5da]/20">
              <tr v-for="conv in aseguradora.convenios" :key="conv.id" class="hover:bg-white/30 transition-colors">
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ conv.nombre }}</td>
                <td class="px-4 py-3 text-sm text-[#4B5563]">{{ conv.tipo_servicio }}</td>
                <td class="px-4 py-3 text-sm"><Badge :variant="conv.estatus === 'vigente' ? 'success' : 'warning'">{{ conv.estatus }}</Badge></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

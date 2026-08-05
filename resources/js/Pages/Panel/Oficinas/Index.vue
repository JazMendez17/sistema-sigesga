<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const busqueda = ref('')

const page = usePage()
const oficinas = computed(() => page.props.oficinas || [])

const filtradas = computed(() => {
  if (!busqueda.value) return oficinas.value
  const q = busqueda.value.toLowerCase()
  return oficinas.value.filter(o => o.nombre?.toLowerCase().includes(q) || o.direccion?.toLowerCase().includes(q) || o.encargado?.toLowerCase().includes(q))
})

function eliminarOficina(id) {
  if (confirm('¿Eliminar esta oficina?')) {
    router.delete(route('panel.oficinas.destroy', { id }))
  }
}
</script>

<template>
  <!-- Directorio de oficinas y sucursales -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Oficinas / Sucursales</h1>
        <NeumorphicButton @click="router.visit(route('panel.oficinas.create'))">
          + Nueva Oficina
        </NeumorphicButton>
      </div>

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div></div>
        <NeumorphicInput
          v-model="busqueda"
          placeholder="Buscar oficina..."
          class="w-full sm:w-64"
        />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div
          v-for="ofi in filtradas"
          :key="ofi.id"
          class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] transition-all hover:shadow-[6px_6px_12px_#d0d5da,-6px_-6px_12px_#ffffff]"
        >
          <div class="flex items-center gap-3 mb-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#EEF2F7] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]">
              <svg class="h-6 w-6 text-[#4F46E5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">{{ ofi.nombre }}</h3>
          </div>
          <div class="space-y-2">
            <div class="flex items-start gap-2">
              <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-[#6B7280]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              <p class="text-sm text-[#4B5563]">{{ ofi.direccion }}</p>
            </div>
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4 flex-shrink-0 text-[#6B7280]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
              <p class="text-sm text-[#4B5563]">{{ ofi.telefono }}</p>
            </div>
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4 flex-shrink-0 text-[#6B7280]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              <p class="text-sm text-[#4B5563]">{{ ofi.email }}</p>
            </div>
            <div class="flex items-center gap-2 pt-2 border-t border-[#d0d5da]/20">
              <svg class="h-4 w-4 flex-shrink-0 text-[#6B7280]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              <p class="text-sm text-[#4B5563]"><span class="text-gray-500">Encargado:</span> <span class="font-medium">{{ ofi.encargado }}</span></p>
            </div>
          </div>
          <div class="mt-4 flex justify-end gap-2">
            <button @click="router.visit(route('panel.oficinas.show', { id: ofi.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#4F46E5]">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            </button>
            <button @click="router.visit(route('panel.oficinas.edit', { id: ofi.id }))" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-[#059669]">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </button>
            <button @click="eliminarOficina(ofi.id)" class="rounded-lg bg-[#EEF2F7] p-2 text-gray-500 shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] transition-all hover:text-red-500">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

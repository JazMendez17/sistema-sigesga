<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const page = usePage()
const tiposServicio = computed(() => page.props.tiposServicio || [])

function toggleActivo(id, current) {
  router.put(route('panel.tipos-servicio.update', { id }), { activo: !current })
}

function toggleManiobra(id, current) {
  router.put(route('panel.tipos-servicio.update', { id }), { requiere_maniobra: !current })
}

function eliminarTipo(id) {
  if (confirm('¿Eliminar este tipo de servicio?')) {
    router.delete(route('panel.tipos-servicio.destroy', { id }))
  }
}
</script>

<template>
  <!-- Catálogo de tipos de servicio -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Tipos de Servicio</h1>
        <NeumorphicButton @click="router.visit(route('panel.tipos-servicio.create'))">
          + Nuevo Tipo
        </NeumorphicButton>
      </div>

      <div class="rounded-3xl bg-[var(--color-surface)] overflow-hidden shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-[var(--neumorphic-dark)]/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">Requiere Maniobra</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">Activo</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[var(--neumorphic-dark)]/20">
              <tr v-for="tipo in tiposServicio" :key="tipo.id" class="hover:bg-white/30 transition-colors">
                <td class="px-4 py-3 text-sm font-medium text-[var(--color-text)]">{{ tipo.nombre }}</td>
                <td class="px-4 py-3 text-sm text-[var(--color-text)]">
                  <button
                    @click="toggleManiobra(tipo.id, tipo.requiere_maniobra)"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                    :class="tipo.requiere_maniobra ? 'bg-[var(--color-primary)] shadow-[inset_2px_2px_4px_#4338ca,inset_-2px_-2px_4px_#6366f1]' : 'bg-gray-300 shadow-[inset_2px_2px_4px_#b0b0b0,inset_-2px_-2px_4px_#d0d0d0]'"
                  >
                    <span
                      class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"
                      :class="tipo.requiere_maniobra ? 'translate-x-6' : 'translate-x-0.5'"
                    />
                  </button>
                </td>
                <td class="px-4 py-3">
                  <button
                    @click="toggleActivo(tipo.id, tipo.activo)"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                    :class="tipo.activo ? 'bg-[#059669] shadow-[inset_2px_2px_4px_#047857,inset_-2px_-2px_4px_#06b684]' : 'bg-gray-300 shadow-[inset_2px_2px_4px_#b0b0b0,inset_-2px_-2px_4px_#d0d0d0]'"
                  >
                    <span
                      class="inline-block h-5 w-5 transform rounded-full bg-white shadow-[2px_2px_4px_#00000020,-2px_-2px_4px_var(--neumorphic-light)] transition-transform duration-200"
                      :class="tipo.activo ? 'translate-x-6' : 'translate-x-0.5'"
                    />
                  </button>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="router.visit(route('panel.tipos-servicio.edit', { id: tipo.id }))" class="rounded-lg bg-[var(--color-surface)] p-2 text-gray-500 shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)] transition-all hover:text-[#4F46E5]">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                    <button @click="router.visit(route('panel.tipos-servicio.edit', { id: tipo.id }))" class="rounded-lg bg-[var(--color-surface)] p-2 text-gray-500 shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)] transition-all hover:text-[#059669]">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                    <button @click="eliminarTipo(tipo.id)" class="rounded-lg bg-[var(--color-surface)] p-2 text-gray-500 shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)] transition-all hover:text-red-500">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

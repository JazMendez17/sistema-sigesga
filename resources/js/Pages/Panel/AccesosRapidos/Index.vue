<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const accesos = computed(() => usePage().props.accesos || [])

function eliminar(id) {
  if (confirm('¿Eliminar esta red social?')) {
    router.delete(route('panel.accesos-rapidos.destroy', id))
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Accesos Rápidos</h1>
          <p class="mt-1 text-sm text-gray-500">Administra las redes sociales de la landing.</p>
        </div>
        <NeumorphicButton @click="router.visit(route('panel.accesos-rapidos.create'))">+ Agregar red</NeumorphicButton>
      </div>

      <div class="overflow-hidden rounded-3xl bg-[var(--color-surface)] shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead><tr class="border-b border-[var(--neumorphic-dark)]/30"><th class="px-4 py-3 text-left text-xs uppercase">Red social</th><th class="px-4 py-3 text-left text-xs uppercase">Enlace</th><th class="px-4 py-3 text-left text-xs uppercase">Estado</th><th class="px-4 py-3 text-right text-xs uppercase">Acciones</th></tr></thead>
            <tbody class="divide-y divide-[var(--neumorphic-dark)]/20">
              <tr v-for="acceso in accesos" :key="acceso.id">
                <td class="flex items-center gap-3 px-4 py-4"><img v-if="acceso.imagen" :src="acceso.imagen" :alt="acceso.titulo" class="h-12 w-12 rounded-xl object-contain shadow-md" /><div><p class="font-semibold">{{ acceso.titulo }}</p><p class="text-xs text-gray-500">{{ acceso.descripcion }}</p></div></td>
                <td class="px-4 py-4 text-sm">{{ acceso.link }}</td>
                <td class="px-4 py-4"><span class="rounded-full px-3 py-1 text-xs" :class="acceso.activo ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500'">{{ acceso.activo ? 'Visible' : 'Oculta' }}</span></td>
                <td class="px-4 py-4 text-right"><button class="mr-3 text-sm text-[var(--color-primary)]" @click="router.visit(route('panel.accesos-rapidos.edit', acceso.id))">Editar</button><button class="text-sm text-red-500" @click="eliminar(acceso.id)">Eliminar</button></td>
              </tr>
              <tr v-if="!accesos.length"><td colspan="4" class="px-4 py-10 text-center text-gray-500">No hay redes sociales configuradas.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

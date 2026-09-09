<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const servicios = computed(() => usePage().props.servicios || [])

function eliminar(id) {
  if (confirm('¿Eliminar este servicio de la landing?')) {
    router.delete(route('panel.servicios-landing.destroy', id))
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-800">Servicios de la Landing</h1><p class="mt-1 text-sm text-gray-500">Solo los servicios activos aparecen en la página pública.</p></div>
        <NeumorphicButton @click="router.visit(route('panel.servicios-landing.create'))">+ Agregar servicio</NeumorphicButton>
      </div>
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <article v-for="servicio in servicios" :key="servicio.id" class="overflow-hidden rounded-3xl bg-[var(--color-surface)] shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
          <img v-if="servicio.foto" :src="'/storage/' + servicio.foto" :alt="servicio.tipo" class="h-40 w-full object-cover" />
          <div v-else class="h-40 w-full" :style="{ backgroundColor: servicio.color || 'var(--color-primary)' }"></div>
          <div class="space-y-3 p-5"><div class="flex items-start justify-between gap-3"><h2 class="font-bold">{{ servicio.tipo }}</h2><span class="rounded-full px-2 py-1 text-xs" :class="servicio.activo ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500'">{{ servicio.activo ? 'Activo' : 'Oculto' }}</span></div><p class="text-sm text-gray-500">{{ servicio.descripcion }}</p><div class="flex justify-end gap-3 text-sm"><button class="text-[var(--color-primary)]" @click="router.visit(route('panel.servicios-landing.edit', servicio.id))">Editar</button><button class="text-red-500" @click="eliminar(servicio.id)">Eliminar</button></div></div>
        </article>
        <p v-if="!servicios.length" class="col-span-full py-12 text-center text-gray-500">No hay servicios configurados.</p>
      </div>
    </div>
  </AppLayout>
</template>

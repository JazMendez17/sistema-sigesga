<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  empleado: { type: Object, required: true },
})
</script>

<template>
  <!-- Perfil detallado del empleado -->
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado con acciones -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ empleado.nombre }} {{ empleado.apellido_paterno }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalles del empleado</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.empleados.index'))">
            Volver
          </NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.empleados.edit', empleado.id))">
            Editar
          </NeumorphicButton>
        </div>
      </div>
      <!-- Tarjeta de datos generales -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos Generales</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Nombre</p><p class="text-sm font-medium text-gray-800">{{ empleado.nombre }} {{ empleado.apellido_paterno }} {{ empleado.apellido_materno }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Teléfono</p><p class="text-sm font-medium text-gray-800">{{ empleado.telefono || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Puesto</p><p class="text-sm font-medium text-gray-800">{{ empleado.puesto || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">CURP</p><p class="text-sm font-medium text-gray-800">{{ empleado.curp || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Oficina</p><p class="text-sm font-medium text-gray-800">{{ empleado.oficina?.nombre || '—' }}</p></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

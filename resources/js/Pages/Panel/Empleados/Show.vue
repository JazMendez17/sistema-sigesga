<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  empleado: { type: Object, required: true },
})

function formato(val) {
  return val || '—'
}
</script>

<template>
  <!-- Perfil detallado del empleado -->
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado con acciones -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ empleado.nombre }} {{ empleado.apellido_paterno }} {{ empleado.apellido_materno ?? '' }}</h1>
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

      <!-- Datos personales -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos Personales</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Nombre Completo</p><p class="text-sm font-medium text-gray-800">{{ empleado.nombre }} {{ empleado.apellido_paterno }} {{ empleado.apellido_materno ?? '' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Sexo</p><p class="text-sm font-medium text-gray-800">{{ empleado.sexo === 'M' ? 'Masculino' : empleado.sexo === 'F' ? 'Femenino' : '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">CURP</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.curp) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Fecha de Nacimiento</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.fecha_nacimiento) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Nacionalidad</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.nacionalidad) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Folio INE</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.folio_ine) }}</p></div>
        </div>
      </div>

      <!-- Datos de contacto -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Contacto</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Teléfono</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.telefono) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Teléfono Local</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.telefono_local) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Correo Electrónico</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.correo) }}</p></div>
        </div>
      </div>

      <!-- Datos laborales -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos Laborales</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Puesto</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.puesto) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Oficina</p><p class="text-sm font-medium text-gray-800">{{ empleado.oficina?.nombre || '—' }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Sueldo Diario</p><p class="text-sm font-medium text-gray-800">{{ empleado.sueldo_diario ? '$' + parseFloat(empleado.sueldo_diario).toFixed(2) : '—' }}</p></div>
        </div>
      </div>

      <!-- Dirección -->
      <div v-if="empleado.direccion" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Dirección</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Calle</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.calle) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Número Exterior</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.numero_exterior) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Número Interior</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.numero_interior) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Colonia</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.colonia) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Código Postal</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.codigo_postal) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Localidad</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.ciudad) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Municipio / Alcaldía</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.municipio_alcaldia) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Estado</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.estado) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">País</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.pais) }}</p></div>
          <div class="md:col-span-3"><p class="text-xs text-gray-500 uppercase tracking-wider">Referencias</p><p class="text-sm font-medium text-gray-800">{{ formato(empleado.direccion.referencias) }}</p></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

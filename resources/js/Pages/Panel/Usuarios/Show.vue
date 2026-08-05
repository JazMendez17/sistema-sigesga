<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
  usuario: { type: Object, required: true },
})

function formato(val) {
  return val || '—'
}

const rolLabel = {
  admin: 'Administrador',
  cotizador: 'Cotizador',
  operador: 'Operador',
  cliente: 'Cliente',
}
</script>

<template>
  <!-- Detalle de usuario -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ usuario.name }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle del usuario</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.usuarios.index'))">Volver</NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.usuarios.edit', usuario.id))">Editar</NeumorphicButton>
        </div>
      </div>

      <!-- Datos del usuario -->
      <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos de Acceso</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Nombre</p><p class="text-sm font-medium text-gray-800">{{ formato(usuario.name) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Email</p><p class="text-sm font-medium text-gray-800">{{ formato(usuario.email) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Rol</p><p class="text-sm font-medium text-gray-800">{{ rolLabel[usuario.rol] || usuario.rol }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Teléfono</p><p class="text-sm font-medium text-gray-800">{{ formato(usuario.telefono) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Intentos Fallidos</p><p class="text-sm font-medium text-gray-800">{{ usuario.intentos_fallidos ?? 0 }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Cuenta Bloqueada</p><Badge :variant="usuario.cuenta_bloqueada ? 'danger' : 'success'">{{ usuario.cuenta_bloqueada ? 'Sí' : 'No' }}</Badge></div>
          <div v-if="usuario.bloqueada_en"><p class="text-xs text-gray-500 uppercase tracking-wider">Fecha de Bloqueo</p><p class="text-sm font-medium text-gray-800">{{ usuario.bloqueada_en }}</p></div>
        </div>
      </div>

      <!-- Datos del empleado vinculado -->
      <div v-if="usuario.empleado" class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Empleado Vinculado</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Nombre Completo</p><p class="text-sm font-medium text-gray-800">{{ formato(usuario.empleado.nombre_completo) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Puesto</p><p class="text-sm font-medium text-gray-800">{{ formato(usuario.empleado.puesto) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Teléfono</p><p class="text-sm font-medium text-gray-800">{{ formato(usuario.empleado.telefono) }}</p></div>
          <div><p class="text-xs text-gray-500 uppercase tracking-wider">Correo</p><p class="text-sm font-medium text-gray-800">{{ formato(usuario.empleado.correo) }}</p></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

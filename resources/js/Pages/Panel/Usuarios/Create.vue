<script setup>
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const props = defineProps({
  usuario: Object,
  empleados: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [
    { value: 'admin', label: 'Admin' },
    { value: 'cotizador', label: 'Cotizador' },
    { value: 'operador', label: 'Operador' },
    { value: 'cliente', label: 'Cliente' },
  ]},
})

const isEdit = !!props.usuario

const form = useForm({
  name: props.usuario?.name ?? '',
  email: props.usuario?.email ?? '',
  password: '',
  rol: props.usuario?.rol ?? '',
  empleado_id: props.usuario?.empleado_id ?? '',
  telefono: props.usuario?.telefono ?? '',
  cuenta_bloqueada: props.usuario?.cuenta_bloqueada ?? false,
})

const rules = {
  name: ['required', 'min:2', 'max:255'],
  email: ['required', 'email', 'max:255'],
  password: isEdit ? [] : ['required', 'min:8'],
  rol: ['required'],
  telefono: ['phone'],
}
const val = useFormValidation(form, rules)

function submit() {
  if (isEdit) {
    form.put(route('panel.usuarios.update', props.usuario.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.usuarios.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ isEdit ? 'Actualiza los datos del usuario' : 'Registra un nuevo usuario' }}</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre</label>
              <NeumorphicInput v-model="form.name" placeholder="Nombre completo" :error="val.getError('name')" @input="val.handleInput('name')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
              <NeumorphicInput v-model="form.email" type="email" placeholder="correo@ejemplo.com" :error="val.getError('email')" @input="val.handleInput('email')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">{{ isEdit ? 'Contraseña (dejar vacío para mantener)' : 'Contraseña' }}</label>
              <NeumorphicInput v-model="form.password" type="password" placeholder="Mínimo 8 caracteres" :error="val.getError('password')" @input="val.handleInput('password')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Rol</label>
              <select v-model="form.rol" @change="val.handleInput('rol')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar rol...</option>
                <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
              </select>
              <p v-if="val.getError('rol')" class="text-sm text-red-500 mt-1">{{ val.getError('rol') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Empleado</label>
              <select v-model="form.empleado_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Sin empleado...</option>
                <option v-for="e in empleados" :key="e.id" :value="e.id">{{ e.nombre }} {{ e.apellido_paterno ?? '' }}</option>
              </select>
            </div>
            <div v-if="!isEdit">
              <label class="block text-sm font-medium text-gray-600 mb-1">Teléfono</label>
              <NeumorphicInput v-model="form.telefono" placeholder="Teléfono" :error="val.getError('telefono')" @input="val.handleInput('telefono')" />
            </div>
          </div>

          <div v-if="isEdit" class="flex items-center gap-2">
            <input id="cuenta_bloqueada" type="checkbox" v-model="form.cuenta_bloqueada" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
            <label for="cuenta_bloqueada" class="text-sm font-medium text-gray-600">Cuenta bloqueada</label>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing" @click="console.log('CLICK EN BOTON GUARDAR')">{{ isEdit ? 'Actualizar Usuario' : 'Guardar Usuario' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.usuarios.index'))">Cancelar</NeumorphicButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card {
  background: #EEF2F7;
  border-radius: 24px;
  box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff;
}
</style>

<script setup>
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const props = defineProps({
  oficina: Object,
})

const isEdit = !!props.oficina

const form = useForm({
  nombre: props.oficina?.nombre ?? '',
  telefono: props.oficina?.telefono ?? '',
  email: props.oficina?.email ?? '',
  encargado: props.oficina?.encargado ?? '',
  calle: props.oficina?.direccion?.calle ?? '',
  numero_exterior: props.oficina?.direccion?.numero_exterior ?? '',
  numero_interior: props.oficina?.direccion?.numero_interior ?? '',
  colonia: props.oficina?.direccion?.colonia ?? '',
  codigo_postal: props.oficina?.direccion?.codigo_postal ?? '',
  municipio_alcaldia: props.oficina?.direccion?.municipio_alcaldia ?? '',
  ciudad: props.oficina?.direccion?.ciudad ?? '',
  estado: props.oficina?.direccion?.estado ?? '',
  pais: props.oficina?.direccion?.pais ?? 'México',
  referencias: props.oficina?.direccion?.referencias ?? '',
})

const rules = {
  nombre: ['required', 'min:2', 'max:255'],
  telefono: ['phone'],
  email: ['email'],
}
const val = useFormValidation(form, rules)

function submit() {
  if (isEdit) {
    form.put(route('panel.oficinas.update', props.oficina.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.oficinas.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Oficina' : 'Nueva Oficina' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ isEdit ? 'Actualiza los datos de la oficina' : 'Registra una nueva oficina' }}</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre</label>
              <NeumorphicInput v-model="form.nombre" placeholder="Nombre de la oficina" :error="val.getError('nombre')" @input="val.handleInput('nombre')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Teléfono</label>
              <NeumorphicInput v-model="form.telefono" placeholder="Ej: 999-123-4567" :error="val.getError('telefono')" @input="val.handleInput('telefono')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
              <NeumorphicInput v-model="form.email" type="email" placeholder="oficina@ejemplo.com" :error="val.getError('email')" @input="val.handleInput('email')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Encargado</label>
              <NeumorphicInput v-model="form.encargado" placeholder="Nombre del encargado" />
            </div>
          </div>

          <div class="border-t border-gray-200 pt-4">
            <p class="text-sm font-medium text-gray-600 mb-3">Dirección</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 mb-1">Calle</label>
                <NeumorphicInput v-model="form.calle" placeholder="Calle" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Número Exterior</label>
                <NeumorphicInput v-model="form.numero_exterior" placeholder="No. exterior" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Número Interior</label>
                <NeumorphicInput v-model="form.numero_interior" placeholder="No. interior (opcional)" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Colonia</label>
                <NeumorphicInput v-model="form.colonia" placeholder="Colonia" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Código Postal</label>
                <NeumorphicInput v-model="form.codigo_postal" placeholder="Código postal" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Municipio / Alcaldía</label>
                <NeumorphicInput v-model="form.municipio_alcaldia" placeholder="Municipio o alcaldía" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Ciudad</label>
                <NeumorphicInput v-model="form.ciudad" placeholder="Ciudad" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Estado</label>
                <NeumorphicInput v-model="form.estado" placeholder="Estado" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">País</label>
                <NeumorphicInput v-model="form.pais" placeholder="País" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 mb-1">Referencias</label>
                <NeumorphicInput v-model="form.referencias" placeholder="Referencias (opcional)" />
              </div>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar Oficina' : 'Guardar Oficina' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.oficinas.index'))">Cancelar</NeumorphicButton>
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

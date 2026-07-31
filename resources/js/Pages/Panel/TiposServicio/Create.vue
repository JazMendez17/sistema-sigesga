<script setup>
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const props = defineProps({
  tipoServicio: Object,
})

const isEdit = !!props.tipoServicio

const form = useForm({
  nombre: props.tipoServicio?.nombre ?? '',
  requiere_maniobra: props.tipoServicio?.requiere_maniobra ?? false,
  activo: props.tipoServicio?.activo ?? true,
})

const rules = {
  nombre: ['required', 'min:2', 'max:255'],
  requiere_maniobra: ['boolean'],
  activo: ['boolean'],
}
const val = useFormValidation(form, rules)

function submit() {
  if (isEdit) {
    form.put(route('panel.tipos-servicio.update', props.tipoServicio.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.tipos-servicio.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Tipo de Servicio' : 'Nuevo Tipo de Servicio' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ isEdit ? 'Actualiza los datos del tipo de servicio' : 'Registra un nuevo tipo de servicio' }}</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre</label>
              <NeumorphicInput v-model="form.nombre" placeholder="Nombre del servicio" :error="val.getError('nombre')" @input="val.handleInput('nombre')" />
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input id="requiere_maniobra" type="checkbox" v-model="form.requiere_maniobra" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
            <label for="requiere_maniobra" class="text-sm font-medium text-gray-600">Requiere Maniobra</label>
          </div>

          <div class="flex items-center gap-2">
            <input id="activo" type="checkbox" v-model="form.activo" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
            <label for="activo" class="text-sm font-medium text-gray-600">Activo</label>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar Tipo de Servicio' : 'Guardar Tipo de Servicio' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.tipos-servicio.index'))">Cancelar</NeumorphicButton>
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

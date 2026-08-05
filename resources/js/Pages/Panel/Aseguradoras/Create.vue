<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const page = usePage()
const aseguradora = page.props.aseguradora
const editMode = !!aseguradora

const form = useForm({
  nombre: aseguradora?.nombre ?? '',
  nombre_comercial: aseguradora?.nombre_comercial ?? '',
  rfc: aseguradora?.rfc ?? '',
  telefono: aseguradora?.telefono ?? '',
})

const rules = {
  nombre: ['required', 'min:2', 'max:255'],
  rfc: ['rfc'],
  telefono: ['phone'],
}
const val = useFormValidation(form, rules)

function submit() {
  if (editMode) {
    form.put(route('panel.aseguradoras.update', aseguradora.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.aseguradoras.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <!-- Formulario de registro / edición de aseguradora -->
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ editMode ? 'Editar Aseguradora' : 'Nueva Aseguradora' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ editMode ? 'Actualiza los datos de la aseguradora' : 'Registra una nueva aseguradora' }}</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre</label>
              <NeumorphicInput v-model="form.nombre" placeholder="Razón social" :error="val.getError('nombre')" @input="val.handleInput('nombre')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre Comercial</label>
              <NeumorphicInput v-model="form.nombre_comercial" placeholder="Nombre comercial" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">RFC</label>
              <NeumorphicInput v-model="form.rfc" placeholder="RFC" :error="val.getError('rfc')" @input="val.handleInput('rfc')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Teléfono</label>
              <NeumorphicInput v-model="form.telefono" placeholder="Teléfono" :error="val.getError('telefono')" @input="val.handleInput('telefono')" />
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ editMode ? 'Actualizar Aseguradora' : 'Guardar Aseguradora' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.aseguradoras.index'))">Cancelar</NeumorphicButton>
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

<script setup>
import { ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const page = usePage()
const aseguradora = page.props.aseguradora
const editMode = !!aseguradora
const submitted = ref(false)

const form = useForm({
  nombre: aseguradora?.nombre ?? '',
  nombre_comercial: aseguradora?.nombre_comercial ?? '',
  rfc: aseguradora?.rfc ?? '',
  telefono: aseguradora?.telefono ?? '',
  contactos: aseguradora?.contactos?.map(c => ({
    departamento: c.departamento ?? '',
    nombre_contacto: c.nombre_contacto ?? '',
    telefono: c.telefono ?? '',
    email: c.email ?? '',
  })) ?? [],
})

function agregarContacto() {
  form.contactos.push({ departamento: '', nombre_contacto: '', telefono: '', email: '' })
}

function eliminarContacto(i) {
  form.contactos.splice(i, 1)
}

const rules = {
  nombre: ['required', 'min:2', 'max:255'],
  rfc: ['rfc'],
  telefono: ['phone'],
}
const val = useFormValidation(form, rules)

function doSubmit() {
  submitted.value = true
  if (!val.validate()) return
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
        <form @submit.prevent="doSubmit" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Razón Social</label>
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

          <!-- Contactos de la aseguradora -->
          <div class="border-t border-gray-200 pt-4">
            <div class="flex items-center justify-between mb-3">
              <p class="text-sm font-medium text-gray-600">Contactos</p>
              <button type="button" @click="agregarContacto" class="rounded-xl bg-[var(--color-bg)] px-3 py-1.5 text-xs text-[var(--color-primary)] shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)]">+ Agregar Contacto</button>
            </div>
            <div v-if="form.contactos.length === 0" class="text-sm text-gray-400 text-center py-3">Sin contactos registrados</div>
            <div v-for="(c, i) in form.contactos" :key="i" class="rounded-2xl bg-[var(--color-bg)] p-4 mb-3 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <NeumorphicInput v-model="c.nombre_contacto" label="Nombre del Contacto" placeholder="Nombre completo" />
                <NeumorphicInput v-model="c.departamento" label="Departamento" placeholder="Ej: Siniestros, Ventas, Cabina" />
                <NeumorphicInput v-model="c.telefono" label="Teléfono" placeholder="Teléfono del contacto" />
                <NeumorphicInput v-model="c.email" label="Email" type="email" placeholder="correo@contacto.com" />
              </div>
              <button type="button" @click="eliminarContacto(i)" class="mt-2 text-xs text-red-500 hover:text-red-600">Eliminar contacto</button>
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

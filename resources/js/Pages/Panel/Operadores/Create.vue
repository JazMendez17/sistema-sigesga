<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const props = defineProps({
  operador: Object,
})

const page = usePage()
const empleados = computed(() => page.props.empleados ?? [])

const isEdit = !!props.operador

const form = useForm({
  empleado_id: props.operador?.empleado_id ?? '',
  tipo_licencia: props.operador?.tipo_licencia ?? '',
  numero_licencia: props.operador?.numero_licencia ?? '',
  fecha_expedicion: props.operador?.fecha_expedicion ?? '',
  fecha_vigencia: props.operador?.fecha_vigencia ?? '',
  disponible: props.operador?.disponible ?? true,
})

const rules = {
  empleado_id: ['required'],
  fecha_expedicion: ['date'],
  fecha_vigencia: ['date', 'after_or_equal:fecha_expedicion'],
  disponible: ['boolean'],
}
const val = useFormValidation(form, rules)

function submit() {
  if (isEdit) {
    form.put(route('panel.operadores.update', props.operador.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.operadores.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <!-- Formulario de registro / edición de operador -->
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Operador' : 'Nuevo Operador' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ isEdit ? 'Actualiza los datos del operador' : 'Registra un nuevo operador' }}</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Empleado</label>
              <select v-model="form.empleado_id" @change="val.handleInput('empleado_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="" disabled>Seleccionar empleado</option>
                <option v-for="e in empleados" :key="e.id" :value="e.id">{{ e.nombre }} {{ e.apellido_paterno }}</option>
              </select>
              <p v-if="val.getError('empleado_id')" class="text-sm text-red-500 mt-1">{{ val.getError('empleado_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Licencia</label>
              <NeumorphicInput v-model="form.tipo_licencia" placeholder="Tipo de licencia" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Número de Licencia</label>
              <NeumorphicInput v-model="form.numero_licencia" placeholder="Número de licencia" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Fecha de Expedición</label>
              <NeumorphicInput v-model="form.fecha_expedicion" type="date" placeholder="Fecha de expedición" :error="val.getError('fecha_expedicion')" @input="val.handleInput('fecha_expedicion')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Fecha de Vigencia</label>
              <NeumorphicInput v-model="form.fecha_vigencia" type="date" placeholder="Fecha de vigencia" :error="val.getError('fecha_vigencia')" @input="val.handleInput('fecha_vigencia')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Disponible</label>
              <select v-model="form.disponible" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option :value="true">Sí</option>
                <option :value="false">No</option>
              </select>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar Operador' : 'Guardar Operador' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.operadores.index'))">Cancelar</NeumorphicButton>
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

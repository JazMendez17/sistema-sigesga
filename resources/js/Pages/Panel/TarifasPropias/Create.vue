<script setup>
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const props = defineProps({
  tarifa: Object,
  tiposServicio: { type: Array, default: () => [] },
})

const isEdit = !!props.tarifa

const form = useForm({
  nombre_tarifa: props.tarifa?.nombre_tarifa ?? '',
  tipo_servicio_id: props.tarifa?.tipo_servicio_id ?? '',
  tipo_ruta: props.tarifa?.tipo_ruta ?? '',
  costo_banderazo: props.tarifa?.costo_banderazo ?? '',
  costo_km: props.tarifa?.costo_km ?? '',
  km_incluidos: props.tarifa?.km_incluidos ?? '',
  cubre_casetas_peaje: props.tarifa?.cubre_casetas_peaje ?? false,
  activo: props.tarifa?.activo ?? true,
})

const rules = {
  nombre_tarifa: ['required', 'min:2', 'max:255'],
  costo_banderazo: ['numeric', 'min_value:0'],
  costo_km: ['numeric', 'min_value:0'],
  km_incluidos: ['numeric', 'min_value:0'],
  cubre_casetas_peaje: ['boolean'],
  activo: ['boolean'],
}
const val = useFormValidation(form, rules)

function submit() {
  if (isEdit) {
    form.put(route('panel.tarifas-propias.update', props.tarifa.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.tarifas-propias.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <!-- Formulario de registro / edición de tarifa propia -->
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Tarifa' : 'Nueva Tarifa Propia' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ isEdit ? 'Actualiza los datos de la tarifa' : 'Registra una nueva tarifa propia' }}</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre de la Tarifa</label>
              <NeumorphicInput v-model="form.nombre_tarifa" placeholder="Ej: Tarifa Ejecutiva" :error="val.getError('nombre_tarifa')" @input="val.handleInput('nombre_tarifa')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Servicio</label>
              <select v-model="form.tipo_servicio_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar tipo...</option>
                <option v-for="ts in tiposServicio" :key="ts.id" :value="ts.id">{{ ts.nombre }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Ruta</label>
              <NeumorphicInput v-model="form.tipo_ruta" placeholder="Ej: Local, Foránea" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Costo Banderazo</label>
              <NeumorphicInput v-model="form.costo_banderazo" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo_banderazo')" @input="val.handleInput('costo_banderazo')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Costo por Km</label>
              <NeumorphicInput v-model="form.costo_km" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo_km')" @input="val.handleInput('costo_km')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Km Incluidos</label>
              <NeumorphicInput v-model="form.km_incluidos" type="number" step="0.01" placeholder="0" :error="val.getError('km_incluidos')" @input="val.handleInput('km_incluidos')" />
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input id="cubre_casetas_peaje" type="checkbox" v-model="form.cubre_casetas_peaje" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
            <label for="cubre_casetas_peaje" class="text-sm font-medium text-gray-600">Cubre Casetas/Peaje</label>
          </div>

          <div class="flex items-center gap-2">
            <input id="activo" type="checkbox" v-model="form.activo" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
            <label for="activo" class="text-sm font-medium text-gray-600">Activo</label>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar Tarifa' : 'Guardar Tarifa' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.tarifas-propias.index'))">Cancelar</NeumorphicButton>
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

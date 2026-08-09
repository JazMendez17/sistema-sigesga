<script setup>
import { ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'
import { showValidationErrors } from '@/stores/notification'

const props = defineProps({
  tarifa: Object,
  tiposServicio: { type: Array, default: () => [] },
})

const isEdit = !!props.tarifa
const submitted = ref(false)

const form = useForm({
  nombre_tarifa: props.tarifa?.nombre_tarifa ?? '',
  tipo_servicio_id: props.tarifa?.tipo_servicio_id ?? '',
  tipo_ruta: props.tarifa?.tipo_ruta ?? '',
  costo_banderazo: props.tarifa?.costo_banderazo ?? '',
  costo_km: props.tarifa?.costo_km ?? '',
  km_incluidos: props.tarifa?.km_incluidos ?? '0.00',
  cubre_casetas_peaje: props.tarifa?.cubre_casetas_peaje ?? false,
  activo: props.tarifa?.activo ?? true,
})

const rules = {
  nombre_tarifa: ['required', 'min:2', 'max:255'],
  tipo_servicio_id: ['required'],
  tipo_ruta: ['required'],
  costo_banderazo: ['required', 'numeric', 'min:0'],
  costo_km: ['required', 'numeric', 'min:0'],
  km_incluidos: ['numeric', 'min:0'],
}
const val = useFormValidation(form, rules)

function doSubmit() {
  submitted.value = true
  if (!val.validate()) {
    const errors = Object.values(val.clientErrors).filter(Boolean)
    if (errors.length) showValidationErrors(errors)
    return
  }
  if (isEdit) {
    form.put(route('panel.tarifas-propias.update', props.tarifa.id), { onSuccess: () => form.reset() })
  } else {
    form.post(route('panel.tarifas-propias.store'), { onSuccess: () => form.reset() })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Tarifa' : 'Nueva Tarifa Propia' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Registra una tarifa propia de la empresa</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="doSubmit" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre de la Tarifa</label>
              <NeumorphicInput v-model="form.nombre_tarifa" placeholder="Ej: Tarifa Ejecutiva VIP" :error="val.getError('nombre_tarifa')" @input="val.handleInput('nombre_tarifa')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Servicio</label>
              <select v-model="form.tipo_servicio_id" @change="val.handleInput('tipo_servicio_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option v-for="ts in tiposServicio" :key="ts.id" :value="ts.id">{{ ts.nombre }}</option>
              </select>
              <p v-if="submitted && val.getError('tipo_servicio_id')" class="text-sm text-red-500 mt-1">{{ val.getError('tipo_servicio_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Ruta</label>
              <select v-model="form.tipo_ruta" @change="val.handleInput('tipo_ruta')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option value="local">Local</option>
                <option value="foraneo">Foráneo</option>
              </select>
              <p v-if="submitted && val.getError('tipo_ruta')" class="text-sm text-red-500 mt-1">{{ val.getError('tipo_ruta') }}</p>
            </div>
            <NeumorphicInput v-model="form.costo_banderazo" label="Costo Banderazo ($)" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo_banderazo')" @input="val.handleInput('costo_banderazo')" />
            <NeumorphicInput v-model="form.costo_km" label="Costo por KM ($)" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo_km')" @input="val.handleInput('costo_km')" />
            <NeumorphicInput v-model="form.km_incluidos" label="KM Incluidos" type="number" step="0.01" placeholder="0.00" :error="val.getError('km_incluidos')" @input="val.handleInput('km_incluidos')" />
          </div>

          <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.cubre_casetas_peaje" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5]" />
              <span class="text-sm font-medium text-gray-600">Cubre Casetas / Peaje</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.activo" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5]" />
              <span class="text-sm font-medium text-gray-600">Activo</span>
            </label>
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
.neumorphic-card { background: #EEF2F7; border-radius: 24px; box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff; }
</style>

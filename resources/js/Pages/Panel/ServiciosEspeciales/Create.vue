<script setup>
import { ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'
import { showValidationErrors } from '@/stores/notification'

const page = usePage()
const maniobra = page.props.maniobra
const convenios = page.props.convenios ?? []
const isEdit = !!maniobra
const submitted = ref(false)

const form = useForm({
  convenio_id: maniobra?.convenio_id ?? '',
  concepto: maniobra?.concepto ?? '',
  aplica: maniobra?.aplica ?? true,
  forma_cobro: maniobra?.forma_cobro ?? '',
  costo: maniobra?.costo ?? '0.00',
})

const rules = {
  convenio_id: ['required'],
  concepto: ['required', 'max:150'],
  costo: ['required', 'numeric', 'min:0'],
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
    form.put(route('panel.servicios-especiales.update', maniobra.id), { onSuccess: () => form.reset() })
  } else {
    form.post(route('panel.servicios-especiales.store'), { onSuccess: () => form.reset() })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Maniobra' : 'Nueva Maniobra Especial' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Registra una maniobra especial asociada a un convenio</p>
      </div>
      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="doSubmit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Convenio</label>
            <select v-model="form.convenio_id" @change="val.handleInput('convenio_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
              <option value="">Seleccionar convenio...</option>
              <option v-for="c in convenios" :key="c.id" :value="c.id">{{ c.nombre_convenio_poliza }}</option>
            </select>
            <p v-if="submitted && val.getError('convenio_id')" class="text-sm text-red-500 mt-1">{{ val.getError('convenio_id') }}</p>
          </div>
          <NeumorphicInput v-model="form.concepto" label="Concepto" placeholder="Ej: Extracción en sótano, Uso de dolly" :error="val.getError('concepto')" @input="val.handleInput('concepto')" />
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Forma de Cobro</label>
            <select v-model="form.forma_cobro" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
              <option value="">Seleccionar...</option>
              <option value="Por Evento">Por Evento</option>
              <option value="Por Hora">Por Hora</option>
              <option value="Por Maniobra">Por Maniobra</option>
              <option value="Por Porcentaje">Por Porcentaje</option>
              <option value="Fijo">Fijo</option>
            </select>
          </div>
          <NeumorphicInput v-model="form.costo" label="Costo ($)" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo')" @input="val.handleInput('costo')" />
          <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" v-model="form.aplica" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5]" />
            <span class="text-sm font-medium text-gray-600">Aplica</span>
          </label>
          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar' : 'Guardar' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.servicios-especiales.index'))">Cancelar</NeumorphicButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: #EEF2F7; border-radius: 24px; box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff; }
</style>

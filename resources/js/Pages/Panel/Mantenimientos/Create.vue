<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const props = defineProps({
  mantenimiento: Object,
})

const page = usePage()
const unidades = computed(() => page.props.unidades ?? [])

const isEdit = !!props.mantenimiento

const form = useForm({
  unidad_id: props.mantenimiento?.unidad_id ?? '',
  tipo: props.mantenimiento?.tipo ?? '',
  fecha: props.mantenimiento?.fecha ?? '',
  kilometraje: props.mantenimiento?.kilometraje ?? '',
  costo: props.mantenimiento?.costo ?? '',
  proximo_mantenimiento_fecha: props.mantenimiento?.proximo_mantenimiento_fecha ?? '',
  proximo_mantenimiento_km: props.mantenimiento?.proximo_mantenimiento_km ?? '',
  observaciones: props.mantenimiento?.observaciones ?? '',
})

const rules = {
  unidad_id: ['required'],
  tipo: ['required', 'min:2', 'max:255'],
  fecha: ['date'],
  kilometraje: ['numeric', 'min_value:0'],
  costo: ['numeric', 'min_value:0'],
  proximo_mantenimiento_fecha: ['date'],
  proximo_mantenimiento_km: ['numeric', 'min_value:0'],
}
const val = useFormValidation(form, rules)

function submit() {
  if (isEdit) {
    form.put(route('panel.mantenimientos.update', props.mantenimiento.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.mantenimientos.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Mantenimiento' : 'Nuevo Mantenimiento' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ isEdit ? 'Actualiza los datos del mantenimiento' : 'Registra un nuevo mantenimiento' }}</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Unidad</label>
              <select v-model="form.unidad_id" @change="val.handleInput('unidad_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="" disabled>Seleccionar unidad</option>
                <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.nombre }}</option>
              </select>
              <p v-if="val.getError('unidad_id')" class="text-sm text-red-500 mt-1">{{ val.getError('unidad_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo</label>
              <NeumorphicInput v-model="form.tipo" placeholder="Ej: Preventivo, Correctivo" :error="val.getError('tipo')" @input="val.handleInput('tipo')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Fecha</label>
              <NeumorphicInput v-model="form.fecha" type="date" :error="val.getError('fecha')" @input="val.handleInput('fecha')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Costo ($)</label>
              <NeumorphicInput v-model="form.costo" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo')" @input="val.handleInput('costo')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Kilometraje</label>
              <NeumorphicInput v-model="form.kilometraje" type="number" placeholder="0" :error="val.getError('kilometraje')" @input="val.handleInput('kilometraje')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Próximo Mantenimiento (Fecha)</label>
              <NeumorphicInput v-model="form.proximo_mantenimiento_fecha" type="date" :error="val.getError('proximo_mantenimiento_fecha')" @input="val.handleInput('proximo_mantenimiento_fecha')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Próximo Mantenimiento (Km)</label>
              <NeumorphicInput v-model="form.proximo_mantenimiento_km" type="number" placeholder="0" :error="val.getError('proximo_mantenimiento_km')" @input="val.handleInput('proximo_mantenimiento_km')" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Observaciones</label>
            <textarea v-model="form.observaciones" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300" rows="3" placeholder="Detalles del mantenimiento..."></textarea>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar Mantenimiento' : 'Guardar Mantenimiento' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.mantenimientos.index'))">Cancelar</NeumorphicButton>
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

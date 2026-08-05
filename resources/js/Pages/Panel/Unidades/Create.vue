<script setup>
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const props = defineProps({
  unidad: Object,
  operadores: { type: Array, default: () => [] },
  oficinas: { type: Array, default: () => [] },
})

const isEdit = !!props.unidad

const form = useForm({
  marca: props.unidad?.marca ?? '',
  tipo: props.unidad?.tipo ?? '',
  modelo: props.unidad?.modelo ?? '',
  placas: props.unidad?.placas ?? '',
  numero_economico: props.unidad?.numero_economico ?? '',
  seguro_vencimiento: props.unidad?.seguro_vencimiento ?? '',
  estado_emplacado: props.unidad?.estado_emplacado ?? '',
  activo: props.unidad?.activo ?? true,
  oficina_id: props.unidad?.oficina_id ?? '',
  operador_asignado_id: props.unidad?.operador_asignado_id ?? '',
})

const rules = {
  marca: ['required', 'min:2', 'max:50'],
  tipo: ['required', 'min:2', 'max:50'],
  modelo: ['max:45'],
  placas: ['required', 'placas'],
  numero_economico: ['required', 'min:2', 'max:50'],
  seguro_vencimiento: ['date'],
  activo: ['boolean'],
}
const val = useFormValidation(form, rules)

function submit() {
  if (isEdit) {
    form.put(route('panel.unidades.update', props.unidad.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.unidades.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <!-- Formulario de registro / edición de unidad -->
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Unidad' : 'Nueva Unidad' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ isEdit ? 'Actualiza los datos de la unidad' : 'Registra una nueva unidad' }}</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Marca</label>
              <NeumorphicInput v-model="form.marca" placeholder="Marca" :error="val.getError('marca')" @input="val.handleInput('marca')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo</label>
              <NeumorphicInput v-model="form.tipo" placeholder="Ej: Sedán, SUV, Camioneta" :error="val.getError('tipo')" @input="val.handleInput('tipo')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Modelo</label>
              <NeumorphicInput v-model="form.modelo" placeholder="Modelo" :error="val.getError('modelo')" @input="val.handleInput('modelo')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Placas</label>
              <NeumorphicInput v-model="form.placas" placeholder="Placas" :error="val.getError('placas')" @input="val.handleInput('placas')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Número Económico</label>
              <NeumorphicInput v-model="form.numero_economico" placeholder="Número económico" :error="val.getError('numero_economico')" @input="val.handleInput('numero_economico')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Seguro Vencimiento</label>
              <NeumorphicInput v-model="form.seguro_vencimiento" type="date" :error="val.getError('seguro_vencimiento')" @input="val.handleInput('seguro_vencimiento')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Estado Emplacado</label>
              <NeumorphicInput v-model="form.estado_emplacado" placeholder="Estado emplacado" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Oficina</label>
              <select v-model="form.oficina_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar oficina...</option>
                <option v-for="o in oficinas" :key="o.id" :value="o.id">{{ o.nombre }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Operador Asignado</label>
              <select v-model="form.operador_asignado_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Sin operador...</option>
                <option v-for="o in operadores" :key="o.id" :value="o.id">{{ o.empleado?.nombre ?? 'Operador #' + o.id }}</option>
              </select>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input id="activo" type="checkbox" v-model="form.activo" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
            <label for="activo" class="text-sm font-medium text-gray-600">Activo</label>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar Unidad' : 'Guardar Unidad' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.unidades.index'))">Cancelar</NeumorphicButton>
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

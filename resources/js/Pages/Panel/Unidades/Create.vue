<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'
import { ESTADOS } from '@/Data/estadosMunicipios'

const props = defineProps({
  unidad: Object,
  operadores: { type: Array, default: () => [] },
  oficinas: { type: Array, default: () => [] },
})

const isEdit = !!props.unidad
const submitted = ref(false)

// Fecha mínima para seguro (mañana)
const tomorrow = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

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
  tipo: ['required'],
  modelo: ['max:45'],
  placas: ['required', 'placas'],
  numero_economico: ['required', 'min:2', 'max:50'],
  seguro_vencimiento: ['date', { rule: 'min_date:today', message: 'La fecha de vencimiento del seguro no puede ser hoy ni una fecha pasada.' }],
  activo: ['boolean'],
}
const val = useFormValidation(form, rules)

function doSubmit() {
  submitted.value = true
  if (!val.validate()) return
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
        <form @submit.prevent="doSubmit" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Marca</label>
              <NeumorphicInput v-model="form.marca" placeholder="Marca" :error="val.getError('marca')" @input="val.handleInput('marca')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo</label>
              <select v-model="form.tipo" @change="val.handleInput('tipo')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar tipo...</option>
                <option value="Arrastre">Arrastre</option>
                <option value="Plataforma">Plataforma</option>
                <option value="Pesada">Pesada</option>
              </select>
              <p v-if="val.getError('tipo')" class="text-sm text-red-500 mt-1">{{ val.getError('tipo') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Modelo</label>
              <NeumorphicInput v-model="form.modelo" placeholder="Modelo" :error="val.getError('modelo')" @input="val.handleInput('modelo')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Placas</label>
              <NeumorphicInput v-model="form.placas" placeholder="Ej: ABC1234" :error="val.getError('placas')" @input="val.handleInput('placas')" />
              <p class="text-xs text-gray-400 mt-1">Formato: 3 a 8 caracteres, solo letras y números (ABC1234, 1234ABC)</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Número Económico</label>
              <NeumorphicInput v-model="form.numero_economico" placeholder="Número económico" :error="val.getError('numero_economico')" @input="val.handleInput('numero_economico')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Seguro Vencimiento</label>
              <NeumorphicInput v-model="form.seguro_vencimiento" type="date" :min="tomorrow" :error="val.getError('seguro_vencimiento')" @input="val.handleInput('seguro_vencimiento')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Estado Emplacado</label>
              <select v-model="form.estado_emplacado" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar estado...</option>
                <option v-for="e in ESTADOS" :key="e" :value="e">{{ e }}</option>
              </select>
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
                <option v-for="o in operadores" :key="o.id" :value="o.id">{{ o.nombre_operador }}</option>
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

<script setup>
import { computed } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const page = usePage()
const clientes = computed(() => page.props.clientes ?? [])
const tiposServicio = computed(() => page.props.tiposServicio ?? [])

const form = useForm({
  cliente_id: '',
  tipo_servicio_id: '',
  origen_direccion: '',
  destino_direccion: '',
  distancia_km: '',
  costo_total: '',
  observaciones: '',
})

const rules = {
  cliente_id: ['required'],
  tipo_servicio_id: ['required'],
  origen_direccion: ['required', 'min:2', 'max:255'],
  destino_direccion: ['max:255'],
  distancia_km: ['numeric'],
  costo_total: ['numeric'],
}
const val = useFormValidation(form, rules)

function submit() {
  form.post(route('panel.cotizaciones.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <!-- Formulario de nueva cotización -->
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Nueva Cotización</h1>
        <p class="text-sm text-gray-500 mt-1">Llena los datos para generar una nueva cotización</p>
      </div>

      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Cliente</label>
              <select v-model="form.cliente_id" @change="val.handleInput('cliente_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar cliente...</option>
                <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }} {{ c.apellido_paterno ?? '' }}</option>
              </select>
              <p v-if="val.getError('cliente_id')" class="text-sm text-red-500 mt-1">{{ val.getError('cliente_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Servicio</label>
              <select v-model="form.tipo_servicio_id" @change="val.handleInput('tipo_servicio_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar tipo de servicio...</option>
                <option v-for="ts in tiposServicio" :key="ts.id" :value="ts.id">{{ ts.nombre }}</option>
              </select>
              <p v-if="val.getError('tipo_servicio_id')" class="text-sm text-red-500 mt-1">{{ val.getError('tipo_servicio_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Origen</label>
              <NeumorphicInput v-model="form.origen_direccion" placeholder="Dirección de origen" :error="val.getError('origen_direccion')" @input="val.handleInput('origen_direccion')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Destino</label>
              <NeumorphicInput v-model="form.destino_direccion" placeholder="Dirección de destino" :error="val.getError('destino_direccion')" @input="val.handleInput('destino_direccion')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Distancia (km)</label>
              <NeumorphicInput v-model="form.distancia_km" type="number" step="0.01" placeholder="0" :error="val.getError('distancia_km')" @input="val.handleInput('distancia_km')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Costo Total</label>
              <NeumorphicInput v-model="form.costo_total" type="number" step="0.01" placeholder="$0.00" :error="val.getError('costo_total')" @input="val.handleInput('costo_total')" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Observaciones</label>
            <textarea v-model="form.observaciones" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300" rows="3" placeholder="Notas adicionales..."></textarea>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">Guardar Cotización</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.cotizaciones.index'))">Cancelar</NeumorphicButton>
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

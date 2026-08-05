<script setup>
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const props = defineProps({
  servicio: Object,
  cotizaciones: { type: Array, default: () => [] },
  operadores: { type: Array, default: () => [] },
  unidades: { type: Array, default: () => [] },
  oficinas: { type: Array, default: () => [] },
})

const isEdit = !!props.servicio

const form = useForm({
  cotizacion_id: props.servicio?.cotizacion_id ?? '',
  operador_id: props.servicio?.operador_id ?? '',
  unidad_id: props.servicio?.unidad_id ?? '',
  oficina_id: props.servicio?.oficina_id ?? '',
  observaciones: props.servicio?.observaciones ?? '',
  estado: props.servicio?.estado ?? 'pendiente',
  kms_salida: props.servicio?.kms_salida ?? '',
  kms_llegada_cliente: props.servicio?.kms_llegada_cliente ?? '',
  kms_termino_servicio: props.servicio?.kms_termino_servicio ?? '',
  kms_regreso_base: props.servicio?.kms_regreso_base ?? '',
  kms_cobrados_reales: props.servicio?.kms_cobrados_reales ?? '',
  cargo_zona_especial: props.servicio?.cargo_zona_especial ?? '',
  costo_final_real: props.servicio?.costo_final_real ?? '',
})

const rules = {
  cotizacion_id: ['required'],
  operador_id: ['required'],
  unidad_id: ['required'],
  oficina_id: ['required'],
  estado: isEdit ? ['required'] : [],
  kms_salida: isEdit ? ['numeric', 'min:0'] : [],
  kms_llegada_cliente: isEdit ? ['numeric', 'min:0'] : [],
  kms_termino_servicio: isEdit ? ['numeric', 'min:0'] : [],
  kms_regreso_base: isEdit ? ['numeric', 'min:0'] : [],
  kms_cobrados_reales: isEdit ? ['numeric', 'min:0'] : [],
  cargo_zona_especial: isEdit ? ['numeric', 'min:0'] : [],
  costo_final_real: isEdit ? ['numeric', 'min:0'] : [],
}
const val = useFormValidation(form, rules)

function submit() {
  if (isEdit) {
    form.put(route('panel.servicios.update', props.servicio.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.servicios.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <!-- Formulario de registro / edición de servicio -->
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado del formulario -->
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Servicio' : 'Nuevo Servicio' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ isEdit ? 'Actualiza los datos del servicio' : 'Registra un nuevo servicio' }}</p>
      </div>

      <!-- Formulario de datos del servicio -->
      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Cotización</label>
              <select v-model="form.cotizacion_id" :disabled="isEdit" @change="val.handleInput('cotizacion_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:opacity-50">
                <option value="">Seleccionar cotización...</option>
                <option v-for="c in cotizaciones" :key="c.id" :value="c.id">{{ c.folio }}</option>
              </select>
              <p v-if="val.getError('cotizacion_id')" class="text-sm text-red-500 mt-1">{{ val.getError('cotizacion_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Operador</label>
              <select v-model="form.operador_id" @change="val.handleInput('operador_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar operador...</option>
                <option v-for="o in operadores" :key="o.id" :value="o.id">{{ o.empleado?.nombre ?? 'Operador #' + o.id }}</option>
              </select>
              <p v-if="val.getError('operador_id')" class="text-sm text-red-500 mt-1">{{ val.getError('operador_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Unidad</label>
              <select v-model="form.unidad_id" @change="val.handleInput('unidad_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar unidad...</option>
                <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.nombre }}</option>
              </select>
              <p v-if="val.getError('unidad_id')" class="text-sm text-red-500 mt-1">{{ val.getError('unidad_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Oficina</label>
              <select v-model="form.oficina_id" @change="val.handleInput('oficina_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar oficina...</option>
                <option v-for="o in oficinas" :key="o.id" :value="o.id">{{ o.nombre }}</option>
              </select>
              <p v-if="val.getError('oficina_id')" class="text-sm text-red-500 mt-1">{{ val.getError('oficina_id') }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-600 mb-1">Observaciones</label>
              <textarea v-model="form.observaciones" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300" rows="3" placeholder="Observaciones del servicio..."></textarea>
            </div>

            <!-- Bitácora de kilometraje (solo en edición) -->
            <template v-if="isEdit">
              <div class="md:col-span-2 border-t border-gray-200 pt-4">
                <p class="text-sm font-medium text-gray-600 mb-3">Bitácora de Kilometraje</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Km Salida</label>
                <NeumorphicInput v-model="form.kms_salida" type="number" step="0.01" placeholder="0" :error="val.getError('kms_salida')" @input="val.handleInput('kms_salida')" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Km Llegada Cliente</label>
                <NeumorphicInput v-model="form.kms_llegada_cliente" type="number" step="0.01" placeholder="0" :error="val.getError('kms_llegada_cliente')" @input="val.handleInput('kms_llegada_cliente')" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Km Término Servicio</label>
                <NeumorphicInput v-model="form.kms_termino_servicio" type="number" step="0.01" placeholder="0" :error="val.getError('kms_termino_servicio')" @input="val.handleInput('kms_termino_servicio')" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Km Regreso Base</label>
                <NeumorphicInput v-model="form.kms_regreso_base" type="number" step="0.01" placeholder="0" :error="val.getError('kms_regreso_base')" @input="val.handleInput('kms_regreso_base')" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Km Cobrados Reales</label>
                <NeumorphicInput v-model="form.kms_cobrados_reales" type="number" step="0.01" placeholder="0" :error="val.getError('kms_cobrados_reales')" @input="val.handleInput('kms_cobrados_reales')" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Cargo Zona Especial</label>
                <NeumorphicInput v-model="form.cargo_zona_especial" type="number" step="0.01" placeholder="0" :error="val.getError('cargo_zona_especial')" @input="val.handleInput('cargo_zona_especial')" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Costo Final Real</label>
                <NeumorphicInput v-model="form.costo_final_real" type="number" step="0.01" placeholder="0" :error="val.getError('costo_final_real')" @input="val.handleInput('costo_final_real')" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Estado</label>
                <select v-model="form.estado" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                  <option value="asignado">Asignado</option>
                  <option value="inicio_servicio">Inicio Servicio</option>
                  <option value="en_sitio_origen">En Sitio Origen</option>
                  <option value="salida_destino">Salida a Destino</option>
                  <option value="en_destino">En Destino</option>
                  <option value="finalizado">Finalizado</option>
                  <option value="solicitud_cancelacion">Solicitud Cancelación</option>
                  <option value="cancelado">Cancelado</option>
                </select>
              </div>
            </template>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar Servicio' : 'Guardar Servicio' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.servicios.index'))">Cancelar</NeumorphicButton>
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

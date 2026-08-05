<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'

const page = usePage()
const convenio = page.props.convenio
const aseguradoras = page.props.aseguradoras ?? []
const tiposServicio = page.props.tiposServicio ?? []

const form = useForm({
  nombre_convenio_poliza: convenio?.nombre_convenio_poliza ?? '',
  aseguradora_id: convenio?.aseguradora_id ?? '',
  tipo_servicio_id: convenio?.tipo_servicio_id ?? '',
  tipo_ruta: convenio?.tipo_ruta ?? '',
  tipo_cobertura: convenio?.tipo_cobertura ?? '',
  alcance_geografico: convenio?.alcance_geografico ?? '',
  costo_banderazo: convenio?.costo_banderazo ?? '',
  costo_km: convenio?.costo_km ?? '',
  km_seguros_incluidos: convenio?.km_seguros_incluidos ?? '',
  km_maximo_amparado: convenio?.km_maximo_amparado ?? '',
  tope_presupuesto: convenio?.tope_presupuesto ?? '',
  cubre_casetas_peaje: convenio?.cubre_casetas_peaje ?? false,
  dias_credito: convenio?.dias_credito ?? '',
  proceso_envio_facturas: convenio?.proceso_envio_facturas ?? '',
  estatus: convenio?.estatus ?? 'vigente',
})

const rules = {
  nombre_convenio_poliza: ['required', 'min:2', 'max:255'],
  costo_banderazo: ['numeric', 'min_value:0'],
  costo_km: ['numeric', 'min_value:0'],
  km_seguros_incluidos: ['numeric', 'min_value:0'],
  km_maximo_amparado: ['numeric', 'min_value:0'],
  tope_presupuesto: ['numeric', 'min_value:0'],
  cubre_casetas_peaje: ['boolean'],
  dias_credito: ['numeric', 'min_value:0'],
}
const val = useFormValidation(form, rules)

function submit() {
  form.put(route('panel.convenios.update', convenio.id), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <!-- Formulario de edición de convenio -->
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Editar Convenio</h1>
        <p class="text-sm text-gray-500 mt-1">Modifica los datos del convenio</p>
      </div>

      <div class="neumorphic-card p-6 max-w-4xl">
        <form @submit.prevent="val.handleSubmit(submit)" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre del Convenio / Póliza</label>
              <NeumorphicInput v-model="form.nombre_convenio_poliza" placeholder="Ej: Convenio General Atlas" :error="val.getError('nombre_convenio_poliza')" @input="val.handleInput('nombre_convenio_poliza')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Aseguradora</label>
              <select v-model="form.aseguradora_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option v-for="a in aseguradoras" :key="a.id" :value="a.id">{{ a.nombre }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Servicio</label>
              <select v-model="form.tipo_servicio_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option v-for="ts in tiposServicio" :key="ts.id" :value="ts.id">{{ ts.nombre }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Ruta</label>
              <select v-model="form.tipo_ruta" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option value="local">Local</option>
                <option value="foraneo">Foráneo</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Cobertura</label>
              <NeumorphicInput v-model="form.tipo_cobertura" placeholder="Ej: Cobertura Amplia" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-600 mb-1">Alcance Geográfico</label>
              <NeumorphicInput v-model="form.alcance_geografico" placeholder="Ej: Nacional, Estatal, Regional" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Costo Banderazo ($)</label>
              <NeumorphicInput v-model="form.costo_banderazo" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo_banderazo')" @input="val.handleInput('costo_banderazo')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Costo por KM ($)</label>
              <NeumorphicInput v-model="form.costo_km" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo_km')" @input="val.handleInput('costo_km')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">KM Seguros Incluidos</label>
              <NeumorphicInput v-model="form.km_seguros_incluidos" type="number" step="0.01" placeholder="0" :error="val.getError('km_seguros_incluidos')" @input="val.handleInput('km_seguros_incluidos')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">KM Máximo Amparado</label>
              <NeumorphicInput v-model="form.km_maximo_amparado" type="number" step="0.01" placeholder="Sin límite" :error="val.getError('km_maximo_amparado')" @input="val.handleInput('km_maximo_amparado')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tope de Presupuesto ($)</label>
              <NeumorphicInput v-model="form.tope_presupuesto" type="number" step="0.01" placeholder="Sin tope" :error="val.getError('tope_presupuesto')" @input="val.handleInput('tope_presupuesto')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Días de Crédito</label>
              <NeumorphicInput v-model="form.dias_credito" type="number" placeholder="0" :error="val.getError('dias_credito')" @input="val.handleInput('dias_credito')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Estatus</label>
              <select v-model="form.estatus" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="vigente">Vigente</option>
                <option value="vencido">Vencido</option>
                <option value="en_negociacion">En Negociación</option>
                <option value="cancelado">Cancelado</option>
              </select>
            </div>
            <div class="flex items-end pb-3">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" v-model="form.cubre_casetas_peaje" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5] checked:shadow-none" />
                <span class="text-sm font-medium text-gray-600">Cubre Casetas de Peaje</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Proceso de Envío de Facturas</label>
            <textarea v-model="form.proceso_envio_facturas" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300" rows="3" placeholder="Describe el proceso de envío de facturas..."></textarea>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">Actualizar Convenio</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.convenios.index'))">Cancelar</NeumorphicButton>
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

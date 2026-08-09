<script setup>
import { ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'
import { showValidationErrors } from '@/stores/notification'

const page = usePage()
const convenio = page.props.convenio
const aseguradoras = page.props.aseguradoras ?? []
const editMode = !!convenio
const submitted = ref(false)

const form = useForm({
  nombre_convenio_poliza: convenio?.nombre_convenio_poliza ?? '',
  codigo_convenio: convenio?.codigo_convenio ?? '',
  aseguradora_id: convenio?.aseguradora_id ?? '',
  fecha_inicio: convenio?.fecha_inicio ?? '',
  fecha_fin: convenio?.fecha_fin ?? '',
  renovacion_automatica: convenio?.renovacion_automatica ?? false,
  exclusivo: convenio?.exclusivo ?? false,
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
  periodicidad_corte: convenio?.periodicidad_corte ?? '',
  requiere_folio_cfdi: convenio?.requiere_folio_cfdi ?? false,
  iva_incluido: convenio?.iva_incluido ?? false,
  tope_credito: convenio?.tope_credito ?? '',
  aviso_previo_terminacion_dias: convenio?.aviso_previo_terminacion_dias ?? '',
  proceso_envio_facturas: convenio?.proceso_envio_facturas ?? '',
})

const rules = {
  nombre_convenio_poliza: ['required', 'min:2', 'max:255'],
  costo_banderazo: ['numeric', 'min:0'],
  costo_km: ['numeric', 'min:0'],
  tope_credito: ['numeric', 'min:0'],
  dias_credito: ['numeric', 'min:0'],
  aviso_previo_terminacion_dias: ['numeric', 'min:0'],
  fecha_inicio: ['date'],
  fecha_fin: ['date'],
}
const val = useFormValidation(form, rules)

function doSubmit() {
  submitted.value = true
  const ok = val.validate()
  if (!ok) {
    const errors = Object.values(val.clientErrors).filter(Boolean)
    if (errors.length) showValidationErrors(errors)
    return
  }
  if (editMode) {
    form.put(route('panel.convenios.update', convenio.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.convenios.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ editMode ? 'Editar Convenio' : 'Nuevo Convenio' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Registra un nuevo convenio con aseguradora</p>
      </div>

      <div class="neumorphic-card p-6 max-w-4xl">
        <form @submit.prevent="doSubmit" class="space-y-5">

          <!-- Datos Generales -->
          <div class="border-b border-gray-200 pb-2"><p class="text-sm font-medium text-gray-600">Datos Generales</p></div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre del Convenio / Póliza</label>
              <NeumorphicInput v-model="form.nombre_convenio_poliza" placeholder="Ej: Convenio General Atlas" :error="val.getError('nombre_convenio_poliza')" @input="val.handleInput('nombre_convenio_poliza')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Código de Convenio</label>
              <NeumorphicInput v-model="form.codigo_convenio" placeholder="Ej: CONV-001" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Aseguradora</label>
              <select v-model="form.aseguradora_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option v-for="a in aseguradoras" :key="a.id" :value="a.id">{{ a.nombre }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Fecha de Inicio</label>
              <NeumorphicInput v-model="form.fecha_inicio" type="date" :error="val.getError('fecha_inicio')" @input="val.handleInput('fecha_inicio')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Fecha de Fin</label>
              <NeumorphicInput v-model="form.fecha_fin" type="date" :error="val.getError('fecha_fin')" @input="val.handleInput('fecha_fin')" />
            </div>
            <div class="flex items-end pb-3"><label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" v-model="form.renovacion_automatica" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5] checked:shadow-none" /><span class="text-sm font-medium text-gray-600">Renovación Automática</span></label></div>
            <div class="flex items-end pb-3"><label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" v-model="form.exclusivo" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5] checked:shadow-none" /><span class="text-sm font-medium text-gray-600">Exclusivo</span></label></div>
          </div>

          <!-- Pagos y Facturación -->
          <div class="border-b border-gray-200 pb-2"><p class="text-sm font-medium text-gray-600">Pagos y Facturación</p></div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Plazo de Pago (días)</label>
              <NeumorphicInput v-model="form.dias_credito" type="number" placeholder="30" :error="val.getError('dias_credito')" @input="val.handleInput('dias_credito')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Periodicidad de Corte</label>
              <select v-model="form.periodicidad_corte" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option value="semanal">Semanal</option>
                <option value="quincenal">Quincenal</option>
                <option value="mensual">Mensual</option>
                <option value="bimestral">Bimestral</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Aviso Previo Terminación (días)</label>
              <NeumorphicInput v-model="form.aviso_previo_terminacion_dias" type="number" placeholder="30" :error="val.getError('aviso_previo_terminacion_dias')" @input="val.handleInput('aviso_previo_terminacion_dias')" />
            </div>
            <div class="flex items-end pb-3"><label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" v-model="form.requiere_folio_cfdi" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5] checked:shadow-none" /><span class="text-sm font-medium text-gray-600">Requiere Folio CFDI</span></label></div>
            <div class="flex items-end pb-3"><label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" v-model="form.iva_incluido" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5] checked:shadow-none" /><span class="text-sm font-medium text-gray-600">IVA Incluido</span></label></div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tope de Crédito ($)</label>
              <NeumorphicInput v-model="form.tope_credito" type="number" step="0.01" placeholder="0.00" :error="val.getError('tope_credito')" @input="val.handleInput('tope_credito')" />
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ editMode ? 'Actualizar Convenio' : 'Guardar Convenio' }}</NeumorphicButton>
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

<script setup>
import { ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'
import { showValidationErrors } from '@/stores/notification'

const page = usePage()
const tarifa = page.props.tarifa
const convenios = page.props.convenios ?? []
const tiposServicio = page.props.tiposServicio ?? []
const isEdit = !!tarifa
const submitted = ref(false)

const form = useForm({
  convenio_id: tarifa?.convenio_id ?? '',
  servicio_id: tarifa?.servicio_id ?? '',
  servicio: tarifa?.servicio ?? '',
  alcance: tarifa?.alcance ?? '',
  banderazo: tarifa?.banderazo ?? '0.00',
  km_incluidos: tarifa?.km_incluidos ?? '0',
  costo_km_extra: tarifa?.costo_km_extra ?? '0.00',
  tarifa_nocturna_recargo_pct: tarifa?.tarifa_nocturna_recargo_pct ?? '0.00',
  tarifa_domingo_festivo_recargo_pct: tarifa?.tarifa_domingo_festivo_recargo_pct ?? '0.00',
  minutos_espera_incluidos: tarifa?.minutos_espera_incluidos ?? '0',
  costo_espera_adicional_hora: tarifa?.costo_espera_adicional_hora ?? '0.00',
  descuento_pct: tarifa?.descuento_pct ?? '0.00',
  tipo_descuento: tarifa?.tipo_descuento ?? '',
})

const rules = {
  convenio_id: ['required'],
  servicio_id: ['required'],
  banderazo: ['numeric', 'min:0'],
  costo_km_extra: ['numeric', 'min:0'],
  tarifa_nocturna_recargo_pct: ['numeric', 'min:0'],
  tarifa_domingo_festivo_recargo_pct: ['numeric', 'min:0'],
  costo_espera_adicional_hora: ['numeric', 'min:0'],
  descuento_pct: ['numeric', 'min:0'],
  km_incluidos: ['numeric', 'min:0'],
  minutos_espera_incluidos: ['numeric', 'min:0'],
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
    form.put(route('panel.convenio-tarifas.update', tarifa.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.convenio-tarifas.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar Tarifa' : 'Nueva Tarifa de Convenio' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Registra una tarifa asociada a un convenio</p>
      </div>

      <div class="neumorphic-card p-6 max-w-3xl">
        <form @submit.prevent="doSubmit" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Convenio</label>
              <select v-model="form.convenio_id" @change="val.handleInput('convenio_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar convenio...</option>
                <option v-for="c in convenios" :key="c.id" :value="c.id">{{ c.nombre_convenio_poliza }}</option>
              </select>
              <p v-if="submitted && val.getError('convenio_id')" class="text-sm text-red-500 mt-1">{{ val.getError('convenio_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Servicio</label>
              <select v-model="form.servicio_id" @change="val.handleInput('servicio_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar servicio...</option>
                <option v-for="ts in tiposServicio" :key="ts.id" :value="ts.id">{{ ts.nombre }}</option>
              </select>
              <p v-if="submitted && val.getError('servicio_id')" class="text-sm text-red-500 mt-1">{{ val.getError('servicio_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Servicio (nombre)</label>
              <NeumorphicInput v-model="form.servicio" placeholder="Ej: Arrastre con gancho" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Alcance</label>
              <NeumorphicInput v-model="form.alcance" placeholder="Ej: Local (Tipo A), Foráneo (Tipo B)" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Banderazo ($)</label>
              <NeumorphicInput v-model="form.banderazo" type="number" step="0.01" placeholder="0.00" :error="val.getError('banderazo')" @input="val.handleInput('banderazo')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">KM Incluidos</label>
              <NeumorphicInput v-model="form.km_incluidos" type="number" placeholder="0" :error="val.getError('km_incluidos')" @input="val.handleInput('km_incluidos')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Costo KM Extra ($)</label>
              <NeumorphicInput v-model="form.costo_km_extra" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo_km_extra')" @input="val.handleInput('costo_km_extra')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Recargo Nocturno (%)</label>
              <NeumorphicInput v-model="form.tarifa_nocturna_recargo_pct" type="number" step="0.01" placeholder="0.00" :error="val.getError('tarifa_nocturna_recargo_pct')" @input="val.handleInput('tarifa_nocturna_recargo_pct')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Recargo Dom/Festivo (%)</label>
              <NeumorphicInput v-model="form.tarifa_domingo_festivo_recargo_pct" type="number" step="0.01" placeholder="0.00" :error="val.getError('tarifa_domingo_festivo_recargo_pct')" @input="val.handleInput('tarifa_domingo_festivo_recargo_pct')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Minutos Espera Incluidos</label>
              <NeumorphicInput v-model="form.minutos_espera_incluidos" type="number" placeholder="0" :error="val.getError('minutos_espera_incluidos')" @input="val.handleInput('minutos_espera_incluidos')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Costo Hora Espera Adicional ($)</label>
              <NeumorphicInput v-model="form.costo_espera_adicional_hora" type="number" step="0.01" placeholder="0.00" :error="val.getError('costo_espera_adicional_hora')" @input="val.handleInput('costo_espera_adicional_hora')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Descuento (%)</label>
              <NeumorphicInput v-model="form.descuento_pct" type="number" step="0.01" placeholder="0.00" :error="val.getError('descuento_pct')" @input="val.handleInput('descuento_pct')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Descuento</label>
              <NeumorphicInput v-model="form.tipo_descuento" placeholder="Ej: Fijo, Escalonado" />
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar Tarifa' : 'Guardar Tarifa' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.convenio-tarifas.index'))">Cancelar</NeumorphicButton>
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

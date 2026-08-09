<script setup>
import { ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { showValidationErrors } from '@/stores/notification'

const page = usePage()
const concepto = page.props.concepto
const convenios = page.props.convenios ?? []
const isEdit = !!concepto
const submitted = ref(false)

const form = useForm({
  convenio_id: concepto?.convenio_id ?? '',
  cubre_casetas: concepto?.cubre_casetas ?? false,
  forma_pago_casetas: concepto?.forma_pago_casetas ?? '',
  costo_estadia_dia: concepto?.costo_estadia_dia ?? '0.00',
  dias_gracia_estadia: concepto?.dias_gracia_estadia ?? '0',
  costo_resguardo_nocturno: concepto?.costo_resguardo_nocturno ?? '0.00',
  genera_cargo_cliente_final: concepto?.genera_cargo_cliente_final ?? false,
})

function doSubmit() {
  submitted.value = true
  if (!form.convenio_id) {
    showValidationErrors(['El convenio es obligatorio.'])
    return
  }
  if (isEdit) {
    form.put(route('panel.conceptos-adicionales.update', concepto.id), { onSuccess: () => form.reset() })
  } else {
    form.post(route('panel.conceptos-adicionales.store'), { onSuccess: () => form.reset() })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar' : 'Nuevos Conceptos Adicionales' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Configura casetas, estancia y resguardo nocturno por convenio</p>
      </div>
      <div class="neumorphic-card p-6 max-w-2xl">
        <form @submit.prevent="doSubmit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Convenio</label>
            <select v-model="form.convenio_id" :disabled="isEdit" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:opacity-50">
              <option value="">Seleccionar convenio...</option>
              <option v-for="c in convenios" :key="c.id" :value="c.id">{{ c.nombre_convenio_poliza }}</option>
            </select>
          </div>

          <div class="border-t border-gray-200 pt-4">
            <p class="text-sm font-medium text-gray-600 mb-3">Casetas</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" v-model="form.cubre_casetas" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5]" />
                <span class="text-sm font-medium text-gray-600">Cubre Casetas</span>
              </label>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Forma de Pago</label>
                <select v-model="form.forma_pago_casetas" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                  <option value="">Seleccionar...</option>
                  <option value="Incluido en tarifa">Incluido en tarifa</option>
                  <option value="Reembolso contra factura">Reembolso contra factura</option>
                </select>
              </div>
            </div>
          </div>

          <div class="border-t border-gray-200 pt-4">
            <p class="text-sm font-medium text-gray-600 mb-3">Estancia y Resguardo</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <NeumorphicInput v-model="form.costo_estadia_dia" label="Costo Estancia / Día ($)" type="number" step="0.01" placeholder="0.00" />
              <NeumorphicInput v-model="form.dias_gracia_estadia" label="Días Gracia Estancia" type="number" placeholder="0" />
              <NeumorphicInput v-model="form.costo_resguardo_nocturno" label="Costo Resguardo Nocturno ($)" type="number" step="0.01" placeholder="0.00" />
              <label class="flex items-center gap-3 cursor-pointer self-end pb-3">
                <input type="checkbox" v-model="form.genera_cargo_cliente_final" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5]" />
                <span class="text-sm font-medium text-gray-600">Genera Cargo a Cliente Final</span>
              </label>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar' : 'Guardar' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.conceptos-adicionales.index'))">Cancelar</NeumorphicButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: #EEF2F7; border-radius: 24px; box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff; }
</style>

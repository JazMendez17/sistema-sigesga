<script setup>
import { ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { showValidationErrors } from '@/stores/notification'

const page = usePage()
const sla = page.props.sla
const convenios = page.props.convenios ?? []
const isEdit = !!sla
const submitted = ref(false)

const form = useForm({
  convenio_id: sla?.convenio_id ?? '',
  tiempo_max_respuesta_urbano_min: sla?.tiempo_max_respuesta_urbano_min ?? '',
  tiempo_max_respuesta_carretera_min: sla?.tiempo_max_respuesta_carretera_min ?? '',
  disponibilidad: sla?.disponibilidad ?? '24/7',
  penalizacion_incumplimiento: sla?.penalizacion_incumplimiento ?? '',
  protocolo_asignacion: sla?.protocolo_asignacion ?? '',
})

function doSubmit() {
  submitted.value = true
  if (!form.convenio_id || !form.tiempo_max_respuesta_urbano_min || !form.tiempo_max_respuesta_carretera_min) {
    showValidationErrors(['Completa todos los campos requeridos.'])
    return
  }
  if (isEdit) {
    form.put(route('panel.penalizaciones.update', sla.id), { onSuccess: () => form.reset() })
  } else {
    form.post(route('panel.penalizaciones.store'), { onSuccess: () => form.reset() })
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Editar SLA' : 'Registrar Penalización (SLA)' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Configura tiempos de respuesta, disponibilidad y penalizaciones por convenio</p>
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

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <NeumorphicInput v-model="form.tiempo_max_respuesta_urbano_min" label="Tiempo Máx. Urbano (min)" type="number" placeholder="30" />
            <NeumorphicInput v-model="form.tiempo_max_respuesta_carretera_min" label="Tiempo Máx. Carretera (min)" type="number" placeholder="60" />
            <NeumorphicInput v-model="form.disponibilidad" label="Disponibilidad" placeholder="24/7" />
            <NeumorphicInput v-model="form.protocolo_asignacion" label="Protocolo de Asignación" placeholder="Ej: Asignación automática al operador más cercano" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Penalización por Incumplimiento</label>
            <textarea v-model="form.penalizacion_incumplimiento" rows="3" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none" placeholder="Describe las cláusulas o montos de penalización por atraso..."></textarea>
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ isEdit ? 'Actualizar SLA' : 'Guardar SLA' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.penalizaciones.index'))">Cancelar</NeumorphicButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: #EEF2F7; border-radius: 24px; box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff; }
</style>

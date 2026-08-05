<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const formServicios = useForm({ fecha_inicio: '', fecha_fin: '' })
const formCostos = useForm({})
const formRendimiento = useForm({ operador: '' })
const formCalificaciones = useForm({})

const generando = ref({})

function generarServicios() {
  generando.value.servicios = true
  formServicios.post(route('panel.reportes.servicios'), {
    preserveScroll: true,
    onFinish: () => setTimeout(() => { generando.value.servicios = false }, 2000),
  })
}

function generarCostos() {
  generando.value.costos = true
  formCostos.post(route('panel.reportes.costos'), {
    preserveScroll: true,
    onFinish: () => setTimeout(() => { generando.value.costos = false }, 2000),
  })
}

function generarRendimiento() {
  generando.value.rendimiento = true
  formRendimiento.post(route('panel.reportes.rendimiento'), {
    preserveScroll: true,
    onFinish: () => setTimeout(() => { generando.value.rendimiento = false }, 2000),
  })
}

function generarCalificaciones() {
  generando.value.calificaciones = true
  formCalificaciones.post(route('panel.reportes.calificaciones'), {
    preserveScroll: true,
    onFinish: () => setTimeout(() => { generando.value.calificaciones = false }, 2000),
  })
}
</script>

<template>
  <!-- Panel de generación de reportes -->
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Reportes</h1>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Servicios por Periodo</h3>
            <p class="text-sm text-gray-500 mt-1">Genera un reporte de todos los servicios realizados en un rango de fechas.</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <NeumorphicInput v-model="formServicios.fecha_inicio" type="date" label="Fecha Inicio" />
            <NeumorphicInput v-model="formServicios.fecha_fin" type="date" label="Fecha Fin" />
          </div>
          <NeumorphicButton @click="generarServicios" variant="secondary" class="w-full" :disabled="formServicios.processing">Generar</NeumorphicButton>
          <div v-if="generando.servicios" class="rounded-2xl bg-[#E8EDF2] p-4 shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] text-sm text-gray-600">
            Generando reporte...
          </div>
        </div>

        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Costos e Ingresos</h3>
            <p class="text-sm text-gray-500 mt-1">Reporte financiero con costos operativos e ingresos por servicio.</p>
          </div>
          <NeumorphicButton @click="generarCostos" variant="secondary" class="w-full" :disabled="formCostos.processing">Generar</NeumorphicButton>
          <div v-if="generando.costos" class="rounded-2xl bg-[#E8EDF2] p-4 shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] text-sm text-gray-600">
            Generando reporte...
          </div>
        </div>

        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Rendimiento por Operador</h3>
            <p class="text-sm text-gray-500 mt-1">Evalúa el desempeño de cada operador según servicios completados.</p>
          </div>
          <NeumorphicInput v-model="formRendimiento.operador" placeholder="Nombre del operador" />
          <NeumorphicButton @click="generarRendimiento" variant="secondary" class="w-full" :disabled="formRendimiento.processing">Generar</NeumorphicButton>
          <div v-if="generando.rendimiento" class="rounded-2xl bg-[#E8EDF2] p-4 shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] text-sm text-gray-600">
            Generando reporte...
          </div>
        </div>

        <div class="rounded-3xl bg-[#EEF2F7] p-6 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] space-y-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Calificaciones Promedio</h3>
            <p class="text-sm text-gray-500 mt-1">Promedio de calificaciones recibidas por servicio o período.</p>
          </div>
          <NeumorphicButton @click="generarCalificaciones" variant="secondary" class="w-full" :disabled="formCalificaciones.processing">Generar</NeumorphicButton>
          <div v-if="generando.calificaciones" class="rounded-2xl bg-[#E8EDF2] p-4 shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] text-sm text-gray-600">
            Generando reporte...
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

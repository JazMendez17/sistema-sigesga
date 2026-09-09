<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({ operador: { type: Object, required: true } })
const nombreEmpleado = () => [props.operador.empleado?.nombre, props.operador.empleado?.apellido_paterno, props.operador.empleado?.apellido_materno].filter(Boolean).join(' ') || 'Operador'
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ nombreEmpleado() }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle de operador</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.operadores.index'))">Volver</NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.operadores.edit', operador.id))">Editar</NeumorphicButton>
        </div>
      </div>
      <div class="neumorphic-card p-6">
        <h2 class="mb-4 text-lg font-semibold text-gray-700">Datos del operador</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div><p class="label">Empleado</p><p class="value">{{ nombreEmpleado() }}</p></div>
          <div><p class="label">Licencia</p><p class="value">{{ operador.numero_licencia || '—' }}</p></div>
          <div><p class="label">Tipo de licencia</p><p class="value">{{ operador.tipo_licencia || '—' }}</p></div>
          <div><p class="label">Vigencia</p><p class="value">{{ operador.fecha_vigencia || '—' }}</p></div>
          <div><p class="label">Disponibilidad</p><p class="value">{{ operador.disponible ? 'Disponible' : 'No disponible' }}</p></div>
          <div><p class="label">Unidad asignada</p><p class="value">{{ operador.unidad?.placas || '—' }}</p></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: var(--color-surface); border-radius: 24px; padding: 1.5rem; box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light); }
.label { color: var(--color-text-muted); font-size: .75rem; text-transform: uppercase; }
.value { color: var(--color-text); font-weight: 500; }
</style>

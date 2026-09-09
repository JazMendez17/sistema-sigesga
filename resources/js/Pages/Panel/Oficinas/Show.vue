<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({ oficina: { type: Object, required: true } })
const direccion = () => props.oficina.direccion || {}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ oficina.nombre || 'Oficina' }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle de oficina</p>
        </div>
        <div class="flex gap-3">
          <NeumorphicButton variant="secondary" @click="router.visit(route('panel.oficinas.index'))">Volver</NeumorphicButton>
          <NeumorphicButton @click="router.visit(route('panel.oficinas.edit', oficina.id))">Editar</NeumorphicButton>
        </div>
      </div>
      <div class="neumorphic-card p-6">
        <h2 class="mb-4 text-lg font-semibold text-gray-700">Datos generales</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div><p class="label">Nombre</p><p class="value">{{ oficina.nombre || '—' }}</p></div>
          <div><p class="label">Teléfono</p><p class="value">{{ oficina.telefono || '—' }}</p></div>
          <div><p class="label">Correo</p><p class="value">{{ oficina.correo || '—' }}</p></div>
          <div class="md:col-span-3"><p class="label">Dirección</p><p class="value">{{ [direccion().calle, direccion().numero_exterior, direccion().colonia, direccion().municipio_alcaldia, direccion().estado, direccion().codigo_postal].filter(Boolean).join(', ') || '—' }}</p></div>
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

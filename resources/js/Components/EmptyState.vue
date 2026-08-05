<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  title: { type: String, default: 'Sin datos' },
  description: { type: String, default: 'No hay información disponible en este momento.' },
  action: { type: Object, default: null },
})
</script>

<!-- Estado vacío para mostrar cuando no hay datos disponibles -->
<template>
  <!-- Contenedor de estado vacío -->
  <div class="neumorphic-raised rounded-3xl p-12 text-center bg-[var(--color-surface)]">
    <!-- Ícono decorativo -->
    <div class="w-20 h-20 mx-auto mb-6 rounded-3xl bg-[var(--color-bg)] flex items-center justify-center shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
      <svg class="w-10 h-10 text-[var(--color-text-placeholder)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
      </svg>
    </div>
    <h3 class="text-lg font-semibold text-[var(--color-text)] mb-2">{{ title }}</h3>
    <p class="text-sm text-[var(--color-text-muted)] mb-6 max-w-md mx-auto">{{ description }}</p>
    <Link
      v-if="action"
      :href="action.url"
      class="inline-flex items-center gap-2 px-6 py-2.5 text-white rounded-2xl text-sm font-medium" :style="{ backgroundColor: 'var(--color-primary)', boxShadow: '0 10px 15px -3px color-mix(in srgb, var(--color-primary) 30%, transparent)' }"
    >
      {{ action.label }}
    </Link>
  </div>
</template>

<style scoped>
.neumorphic-raised {
  box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light);
}
</style>

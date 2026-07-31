<script setup>
defineProps({
  title: String,
  value: [String, Number],
  icon: String,
  color: { type: String, default: '#4F46E5' },
  trend: { type: Object, default: null },
})
</script>

<template>
  <div class="neumorphic-raised rounded-3xl p-6 bg-[var(--color-surface)] relative overflow-hidden">
    <div class="absolute top-0 left-0 right-0 h-1" :style="{ backgroundColor: 'var(--color-secondary)' }"></div>
    <div class="flex items-start justify-between mb-4">
      <div class="w-12 h-12 rounded-2xl flex items-center justify-center" :style="{ backgroundColor: color + '20' }">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color }" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
        </svg>
      </div>
      <div v-if="trend" class="flex items-center gap-1 text-sm" :class="trend.isUp ? 'text-green-600' : 'text-red-500'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="trend.isUp ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'" />
        </svg>
        <span class="font-medium">{{ trend.value }}</span>
      </div>
    </div>
    <p class="text-3xl font-bold text-[var(--color-text)] mb-1">{{ value }}</p>
    <p class="text-sm text-[var(--color-text-muted)]">{{ title }}</p>
  </div>
</template>

<style scoped>
.neumorphic-raised {
  box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light);
}
</style>

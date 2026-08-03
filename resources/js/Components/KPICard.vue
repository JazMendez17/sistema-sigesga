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
  <div class="group relative overflow-hidden rounded-[28px] border border-white/40 bg-[var(--color-surface)] p-5 shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[12px_12px_24px_var(--neumorphic-dark),-12px_-12px_24px_var(--neumorphic-light)]">
    <div class="absolute inset-x-0 top-0 h-1" :style="{ background: 'linear-gradient(90deg, var(--color-primary), var(--color-secondary))' }"></div>
    <div class="mb-4 flex items-start justify-between gap-3">
      <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/30" :style="{ backgroundColor: color + '18', boxShadow: 'inset 2px 2px 6px rgba(255,255,255,0.35), inset -2px -2px 6px rgba(15,23,42,0.04)' }">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color }" stroke-width="1.7">
          <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
        </svg>
      </div>
      <div v-if="trend" class="flex items-center gap-1 rounded-full bg-[var(--color-bg)] px-2 py-1 text-xs font-semibold shadow-[inset_2px_2px_6px_var(--neumorphic-dark),inset_-2px_-2px_6px_var(--neumorphic-light)]" :class="trend.isUp ? 'text-green-600' : 'text-red-500'">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="trend.isUp ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'" />
        </svg>
        <span>{{ trend.value }}</span>
      </div>
    </div>
    <p class="mb-1 text-3xl font-bold tracking-tight text-[var(--color-text)]">{{ value }}</p>
    <p class="text-sm text-[var(--color-text-muted)]">{{ title }}</p>
  </div>
</template>

<style scoped>
</style>

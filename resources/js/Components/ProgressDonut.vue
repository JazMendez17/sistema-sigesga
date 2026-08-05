<script setup>
import { computed } from 'vue'

const props = defineProps({
  percentage: { type: Number, default: 73 },
  size: { type: Number, default: 140 },
  strokeWidth: { type: Number, default: 10 },
  color: { type: String, default: 'url(#progressGradient)' },
})

const radius = computed(() => (props.size - props.strokeWidth) / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)
const offset = computed(() => circumference.value - (props.percentage / 100) * circumference.value)
const center = computed(() => props.size / 2)
</script>

<!-- Gráfico de progreso circular tipo dona con gradiente y efecto glow -->
<template>
  <!-- Contenedor neumórfico circular -->
  <div
    class="relative inline-flex items-center justify-center overflow-hidden rounded-full border border-white/30 bg-[var(--color-bg)] shadow-[10px_10px_20px_var(--neumorphic-dark),-10px_-10px_20px_var(--neumorphic-light)]"
    :style="{ width: size + 'px', height: size + 'px' }"
  >
    <!-- Anillo interior hundido -->
    <div
      class="absolute inset-3 rounded-full bg-[var(--color-bg)] shadow-[inset_8px_8px_16px_var(--neumorphic-dark),inset_-8px_-8px_16px_var(--neumorphic-light)]"
    ></div>
    <!-- SVG de la dona -->
    <svg :width="size" :height="size" class="relative z-10 transform -rotate-90 overflow-visible">
      <defs>
        <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" stop-color="var(--color-primary)" />
          <stop offset="100%" stop-color="var(--color-secondary)" />
        </linearGradient>
        <filter id="glow">
          <feGaussianBlur stdDeviation="3" result="coloredBlur" />
          <feMerge>
            <feMergeNode in="coloredBlur" />
            <feMergeNode in="SourceGraphic" />
          </feMerge>
        </filter>
      </defs>
      <circle
        :cx="center"
        :cy="center"
        :r="radius"
        fill="none"
        stroke="rgba(148, 163, 184, 0.22)"
        :stroke-width="strokeWidth + 8"
        stroke-linecap="round"
      />
      <circle
        :cx="center"
        :cy="center"
        :r="radius"
        fill="none"
        stroke="rgba(255,255,255,0.8)"
        :stroke-width="strokeWidth + 2"
        stroke-linecap="round"
      />
      <circle
        :cx="center"
        :cy="center"
        :r="radius"
        fill="none"
        :stroke="color"
        :stroke-width="strokeWidth"
        stroke-linecap="round"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="offset"
        filter="url(#glow)"
        class="transition-all duration-1000 ease-out"
      />
    </svg>
    <!-- Porcentaje en el centro -->
    <div class="absolute inset-0 z-20 flex items-center justify-center">
      <span class="text-2xl font-bold text-[var(--color-text)]">{{ percentage }}%</span>
    </div>
  </div>
</template>

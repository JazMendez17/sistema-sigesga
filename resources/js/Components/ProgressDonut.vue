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

<template>
  <div class="relative inline-flex items-center justify-center" :style="{ width: size + 'px', height: size + 'px' }">
    <svg :width="size" :height="size" class="transform -rotate-90">
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
        stroke="var(--color-bg)"
        :stroke-width="strokeWidth"
        class="shadow-[inset_4px_4px_8px_var(--neumorphic-dark)]"
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
    <div class="absolute inset-0 flex items-center justify-center">
      <span class="text-2xl font-bold text-[var(--color-text)]">{{ percentage }}%</span>
    </div>
  </div>
</template>

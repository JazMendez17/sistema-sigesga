<script setup>
const props = defineProps({
  variant: { type: String, default: 'neutral' },
  size: { type: String, default: 'sm' },
})

const sd = 'shadow-[2px_2px_4px_var(--neumorphic-dark),-2px_-2px_4px_var(--neumorphic-light)]'

const variants = {
  success: `bg-green-100 text-green-700 ${sd}`,
  warning: `bg-amber-100 text-amber-700 ${sd}`,
  danger: `bg-red-100 text-red-700 ${sd}`,
  info: `bg-[var(--color-secondary-light)] text-[var(--color-secondary)] ${sd}`,
  neutral: `bg-gray-100 text-gray-700 ${sd}`,
}

const sizes = {
  sm: 'px-2 py-0.5 text-xs',
  md: 'px-3 py-1 text-sm',
}
</script>

<template>
  <span :class="[variants[variant], sizes[size], 'inline-flex items-center font-medium rounded-xl']">
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'primary' },
  size: { type: String, default: 'md' },
  loading: Boolean,
})

defineEmits(['click'])

const baseClasses = 'inline-flex items-center justify-center gap-2 font-medium rounded-2xl transition-all duration-200 disabled:opacity-50 cursor-pointer'

const sizes = {
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-5 py-2.5 text-sm',
  lg: 'px-7 py-3 text-base',
}

const variantStyle = computed(() => {
  if (props.variant === 'primary') {
    return {
      backgroundColor: 'var(--color-primary)',
      color: '#fff',
      boxShadow: '0 10px 15px -3px color-mix(in srgb, var(--color-primary, #4F46E5) 40%, transparent), 0 4px 6px -4px color-mix(in srgb, var(--color-primary, #4F46E5) 40%, transparent)',
    }
  }
  return {}
})

const variantClass = computed(() => {
  if (props.variant === 'secondary') return 'neumorphic-raised text-[var(--color-text)]'
  if (props.variant === 'danger') return 'bg-gradient-to-br from-red-500 to-rose-600 text-white shadow-lg shadow-red-200'
  if (props.variant === 'ghost') return 'text-[var(--color-text)] hover:neumorphic-raised'
  return ''
})
</script>

<template>
  <button
    @click="$emit('click')"
    :disabled="loading"
    :style="variantStyle"
    :class="[
      variantClass,
      sizes[size],
      baseClasses,
    ]"
  >
    <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
    </svg>
    <slot />
  </button>
</template>

<style scoped>
.neumorphic-raised {
  box-shadow: 4px 4px 8px var(--neumorphic-dark, #d0d5da), -4px -4px 8px var(--neumorphic-light, #ffffff);
}
</style>

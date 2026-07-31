<script setup>
const model = defineModel()

defineProps({
  label: String,
  type: { type: String, default: 'text' },
  placeholder: String,
  icon: String,
  error: String,
  required: Boolean,
})
</script>

<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-[var(--color-text)] mb-2">
      {{ label }}<span v-if="required" class="text-[var(--color-danger)] ml-1">*</span>
    </label>
    <div class="relative">
      <div v-if="icon" class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
        <svg class="w-5 h-5 text-[var(--color-text-placeholder)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
        </svg>
      </div>
      <input
        :type="type"
        :placeholder="placeholder"
        :required="required"
        :value="model"
        @input="model = $event.target.value"
        :class="[
          'w-full bg-[var(--color-bg)] text-[var(--color-text)] placeholder-[var(--color-text-placeholder)] rounded-2xl py-3 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]',
          icon ? 'pl-12 pr-4' : 'px-4',
          'shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)]',
        ]"
      />
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
  </div>
</template>

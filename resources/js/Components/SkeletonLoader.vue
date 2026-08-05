<script setup>
defineProps({
  type: { type: String, default: 'text' },
  width: { type: String, default: '' },
  height: { type: String, default: '' },
  rounded: { type: String, default: '' },
  count: { type: Number, default: 1 },
  circle: { type: Boolean, default: false },
})
</script>

<template>
  <template v-for="i in count" :key="i">
    <div
      v-if="type === 'circle'"
      class="animate-pulse rounded-full"
      :class="[rounded || 'rounded-full']"
      :style="{ width: width || '3rem', height: height || width || '3rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.3 }"
    />
    <div
      v-else-if="type === 'text'"
      class="animate-pulse"
      :class="[rounded || 'rounded-xl']"
      :style="{ width: width || '100%', height: height || '1rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.3 }"
    />
    <div
      v-else-if="type === 'title'"
      class="animate-pulse"
      :class="[rounded || 'rounded-xl']"
      :style="{ width: width || '60%', height: height || '1.5rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.3 }"
    />
    <div
      v-else-if="type === 'button'"
      class="animate-pulse"
      :class="[rounded || 'rounded-2xl']"
      :style="{ width: width || '10rem', height: height || '2.75rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.3 }"
    />
    <div
      v-else-if="type === 'card'"
      class="animate-pulse rounded-3xl p-6 space-y-4"
      :style="{ width: width || '100%', height: height || 'auto', backgroundColor: 'var(--color-surface)', boxShadow: '8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light)' }"
    >
      <div class="rounded-xl" :style="{ width: '50%', height: '1.25rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.25 }" />
      <div class="rounded-xl" :style="{ width: '80%', height: '0.875rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.2 }" />
      <div class="rounded-xl" :style="{ width: '70%', height: '0.875rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.2 }" />
      <div class="rounded-xl" :style="{ width: '40%', height: '0.875rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.2 }" />
    </div>
    <div
      v-else-if="type === 'table-row'"
      class="animate-pulse rounded-2xl flex items-center gap-4 px-4"
      :style="{ height: height || '3rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.15 + (i * 0.03) }"
    >
      <div class="rounded-lg" :style="{ width: '15%', height: '0.75rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.25 }" />
      <div class="rounded-lg" :style="{ width: '20%', height: '0.75rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.2 }" />
      <div class="rounded-lg" :style="{ width: '25%', height: '0.75rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.2 }" />
      <div class="rounded-lg" :style="{ width: '15%', height: '0.75rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.2 }" />
      <div class="rounded-lg ml-auto" :style="{ width: '8%', height: '0.75rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.15 }" />
    </div>
    <div
      v-else-if="type === 'stat-card'"
      class="animate-pulse rounded-[22px] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]"
      :style="{ backgroundColor: 'var(--color-bg)' }"
    >
      <div class="rounded-lg mb-3" :style="{ width: '40%', height: '0.625rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.2 }" />
      <div class="rounded-lg" :style="{ width: '30%', height: '1.5rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.3 }" />
    </div>
    <div
      v-else-if="type === 'form-field'"
      class="animate-pulse space-y-2"
      :style="{ width: width || '100%' }"
    >
      <div class="rounded-lg" :style="{ width: '30%', height: '0.75rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.25 }" />
      <div class="rounded-2xl" :style="{ width: '100%', height: height || '2.75rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.15 }" />
    </div>
    <div
      v-else-if="type === 'block'"
      class="animate-pulse"
      :class="[rounded || 'rounded-2xl']"
      :style="{ width: width || '100%', height: height || '5rem', backgroundColor: 'var(--neumorphic-dark)', opacity: 0.2 }"
    />
  </template>
</template>

<script setup>
import { useTheme } from '@/Composables/useTheme'
import Navbar from '@/Components/Navbar.vue'
import Toast from '@/Components/Toast.vue'

useTheme()
</script>

<template>
  <div class="min-h-screen bg-[var(--color-bg)]">
    <div class="fixed top-0 left-0 right-0 z-50 h-1" :style="{ backgroundColor: 'var(--color-secondary)' }"></div>
    <Navbar />
    <Toast />

    <main class="min-h-screen pt-16">
      <div class="p-4 sm:p-6 lg:p-8">
        <div class="page-container mx-auto w-full" style="max-width: 1120px;">
          <slot />
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.page-container :deep(.neumorphic-card) {
  max-width: 100% !important;
  width: 100%;
}
</style>
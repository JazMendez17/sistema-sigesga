<script setup>
import { useTheme } from '@/Composables/useTheme'
import { usePageLoading } from '@/Composables/usePageLoading'
import Navbar from '@/Components/Navbar.vue'
import Toast from '@/Components/Toast.vue'
import SkeletonLoader from '@/Components/SkeletonLoader.vue'

useTheme()
const { loading } = usePageLoading()
</script>

<template>
  <div class="min-h-screen bg-[var(--color-bg)]">
    <div class="fixed top-0 left-0 right-0 z-50 h-1.5" :style="{ backgroundColor: 'var(--color-secondary)' }"></div>
    <Navbar />
    <Toast />

    <main class="min-h-screen pt-16">
      <div class="p-4 sm:p-6 lg:p-8">
        <div class="page-container mx-auto w-full" style="max-width: 1120px;">
          <template v-if="loading">
            <div class="space-y-6">
              <SkeletonLoader type="title" width="30%" />
              <SkeletonLoader type="text" width="50%" />
              <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <SkeletonLoader type="stat-card" v-for="n in 3" :key="n" />
              </div>
              <SkeletonLoader type="card" />
              <div class="space-y-3">
                <SkeletonLoader type="table-row" v-for="n in 6" :key="n" />
              </div>
            </div>
          </template>
          <slot v-else />
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
<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const page = usePage()
const integraciones = computed(() => page.props.integraciones || [])

function toggleActivo(id, current) {
  router.put(route('panel.integraciones.update', { id }), { activo: !current })
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Integraciones</h1>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="item in integraciones"
          :key="item.id"
          class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-4"
        >
          <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]">
              <svg class="h-7 w-7 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" /></svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-800">{{ item.titulo }}</h3>
              <Badge :variant="item.configurado ? 'success' : 'neutral'">{{ item.configurado ? 'Configurado' : 'Sin configurar' }}</Badge>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Activo</span>
            <button
              @click="toggleActivo(item.id, item.activo)"
              class="relative h-7 w-12 rounded-full transition-colors duration-200"
              :class="item.activo ? 'bg-[var(--color-primary)]' : 'bg-gray-300'"
            >
              <span
                class="absolute left-0.5 top-0.5 h-6 w-6 rounded-full bg-white shadow transition-transform duration-200"
                :class="item.activo ? 'translate-x-5' : 'translate-x-0'"
              ></span>
            </button>
          </div>

          <NeumorphicButton variant="secondary" class="w-full" @click="router.visit(route('panel.configuracion.index'))">Configurar</NeumorphicButton>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

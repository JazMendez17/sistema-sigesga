<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const page = usePage()
const integraciones = computed(() => page.props.integraciones || [])

const iconos = {
  whatsapp: 'M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z',
  mail: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
  map: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
}

function iconPath(item) {
  return iconos[item.icono] || 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
}

function toggleActivo(id, current) {
  router.put(route('panel.integraciones.update', { id }), { activo: !current })
}
</script>

<template>
  <!-- Panel de integraciones externas (WhatsApp, email, SMS) -->
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
              <svg class="h-7 w-7 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="iconPath(item)" /></svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-800">{{ item.titulo }}</h3>
              <Badge :variant="item.configurado ? 'success' : 'neutral'">{{ item.configurado ? 'Configurado' : 'Sin configurar' }}</Badge>
            </div>
          </div>

          <p class="min-h-[2.5rem] text-sm text-gray-500">{{ item.descripcion || 'Integración con servicio externo.' }}</p>

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

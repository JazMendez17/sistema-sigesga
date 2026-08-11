<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

defineProps({ servicios: Array })
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold text-gray-800">Evaluar Servicios</h1>
      <p class="text-sm text-gray-500">Califica los servicios que han sido finalizados</p>

      <div v-if="servicios?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="s in servicios" :key="s.id" class="rounded-2xl bg-[#EEF2F7] p-5 shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] space-y-3">
          <div class="flex justify-between"><span class="text-gray-500 text-sm">{{ s.folio }}</span><span class="text-sm text-gray-400">{{ s.fecha }}</span></div>
          <p class="font-medium text-gray-800">{{ s.tipo }}</p>
          <p class="text-sm text-gray-500">{{ s.monto }}</p>
          <NeumorphicButton @click="router.visit(route('panel.servicios.evaluar', { id: s.id }))" class="w-full">⭐ Evaluar Servicio</NeumorphicButton>
        </div>
      </div>
      <p v-else class="text-gray-400 text-center py-12">No tienes servicios pendientes de evaluar.</p>
    </div>
  </AppLayout>
</template>

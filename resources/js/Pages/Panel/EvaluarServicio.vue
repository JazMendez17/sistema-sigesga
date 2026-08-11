<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme'

useTheme()

const props = defineProps({ servicio: Object })
const estrellasSeleccionadas = ref(0)

const form = useForm({
  estrellas: 0,
  comentario: '',
})

function seleccionar(n) {
  estrellasSeleccionadas.value = n
  form.estrellas = n
}

function submit() {
  if (!form.estrellas) return
  form.post(route('panel.servicios.evaluar.store', { id: props.servicio.id }))
}
</script>

<template>
  <Head title="Evaluar Servicio" />
  <div class="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-[var(--color-text)]">Evaluar Servicio</h1>
        <p class="text-sm text-[var(--color-text-muted)] mt-1">{{ servicio?.folio }} - {{ servicio?.tipo }}</p>
      </div>

      <div class="neumorphic-card p-8 text-center">
        <p class="text-sm text-gray-500 mb-6">¿Cómo calificarías la atención recibida?</p>
        <div class="flex justify-center gap-2 mb-6">
          <button v-for="n in 5" :key="n" @click="seleccionar(n)" class="text-4xl transition-transform duration-200 hover:scale-125"
            :class="n <= estrellasSeleccionadas ? 'text-yellow-400 scale-110' : 'text-gray-300'">
            ★
          </button>
        </div>

        <div class="mb-5">
          <textarea v-model="form.comentario" rows="3" placeholder="Deja tu comentario (opcional)"
            class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] resize-none"></textarea>
        </div>

        <button @click="submit" :disabled="form.processing || !form.estrellas"
          class="w-full py-3 px-6 text-white font-medium rounded-2xl transition-all duration-200 disabled:opacity-50"
          :style="{ backgroundColor: 'var(--color-primary)' }">
          <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2 align-middle"></span>
          Enviar Evaluación
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.neumorphic-card { background: var(--color-surface); border-radius: 24px; box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light); }
</style>

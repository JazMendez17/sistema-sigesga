<script setup>
import { ref, watch, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { validationErrors, clearValidationErrors } from '@/stores/notification'

const page = usePage()
const visible = ref(false)
const message = ref('')
const errorsList = ref([])
const type = ref('success')
let timer = null

function close() {
  visible.value = false
  if (timer) clearTimeout(timer)
}

function getLabel() {
  const msg = message.value.toLowerCase()
  if (msg.includes('error') || msg.includes('err')) return 'Error'
  if (msg.includes('elimin') || msg.includes('borr')) return 'Eliminado'
  if (msg.includes('actualiz') || msg.includes('modific')) return 'Actualizado'
  if (msg.includes('cre') || msg.includes('registr')) return 'Creado'
  if (msg.includes('guard') || msg.includes('correctament')) return 'Guardado'
  if (msg.includes('sub') || msg.includes('carg')) return 'Subido'
  return 'Correcto'
}

function show(msg, t, duration) {
  if (timer) clearTimeout(timer)
  message.value = msg
  type.value = t
  visible.value = true
  timer = setTimeout(() => { visible.value = false }, duration || 4000)
}

function showErrors(errors, duration) {
  if (timer) clearTimeout(timer)
  errorsList.value = errors
  message.value = ''
  type.value = 'error'
  visible.value = true
  timer = setTimeout(() => { visible.value = false }, duration || 6000)
}

watch(() => page.props.flash, (flash) => {
  const msg = flash?.success || flash?.error || ''
  if (!msg) return
  errorsList.value = []
  show(msg, flash?.error ? 'error' : 'success')
}, { deep: true })

watch(() => page.props.errors, (errors) => {
  const msgs = Object.values(errors).filter(Boolean)
  if (msgs.length === 0) return
  showErrors(msgs)
}, { deep: true })

watch(validationErrors, (errors) => {
  if (errors.length === 0) return
  showErrors(errors)
  clearValidationErrors()
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 scale-90 translate-y-4"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-90"
    >
      <div
        v-if="visible"
        class="fixed inset-0 z-[9999] flex items-center justify-center pointer-events-none"
      >
        <div
          class="pointer-events-auto max-w-sm w-full mx-4 rounded-3xl p-6 shadow-[12px_12px_24px_var(--neumorphic-dark,#b0b5ba),-12px_-12px_24px_var(--neumorphic-light,#ffffff)] text-center relative"
          :class="type === 'error' ? 'bg-red-50' : 'bg-[var(--color-surface,#EEF2F7)]'"
        >
          <div
            class="w-14 h-14 mx-auto mb-3 rounded-2xl flex items-center justify-center"
            :class="type === 'error' ? 'bg-red-100' : ''"
            :style="type !== 'error' ? { backgroundColor: 'var(--color-primary)' } : {}"
          >
            <svg v-if="type === 'error'" class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <svg v-else class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <button @click="close" class="absolute top-4 right-4 w-7 h-7 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-[var(--color-bg)] transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <h3 class="text-lg font-bold" :class="type === 'error' ? 'text-red-800' : 'text-[var(--color-text,#1F2937)]'">
            {{ type === 'error' ? 'Errores de validación' : getLabel() }}
          </h3>
          <p v-if="message" class="text-sm mt-1 opacity-70" :class="type === 'error' ? 'text-red-600' : 'text-[var(--color-text,#1F2937)]'">{{ message }}</p>
          <ul v-else-if="errorsList.length > 0" class="mt-2 space-y-1 text-left">
            <li v-for="(err, i) in errorsList" :key="i" class="text-sm text-red-600 flex items-start gap-2">
              <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-red-400 flex-shrink-0"></span>
              <span>{{ err }}</span>
            </li>
          </ul>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

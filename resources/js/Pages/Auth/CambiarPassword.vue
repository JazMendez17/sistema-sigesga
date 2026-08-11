<script setup>
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme'
import { usePasswordStrength } from '@/Composables/usePasswordStrength'
import PasswordStrengthIndicator from '@/Components/PasswordStrengthIndicator.vue'
import { showValidationErrors } from '@/stores/notification'

useTheme()

const submitted = ref(false)

const form = useForm({
  password: '',
  password_confirmation: '',
})

const { resultados, level, isValid, errores } = usePasswordStrength(computed(() => form.password))

const submit = () => {
  submitted.value = true
  if (!isValid.value) {
    showValidationErrors(['La contraseña no cumple con los requisitos de seguridad.'])
    return
  }
  if (form.password !== form.password_confirmation) {
    showValidationErrors(['Las contraseñas no coinciden.'])
    return
  }
  form.post(route('password.cambiar.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <Head title="Cambiar Contraseña" />
  <div class="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-white font-bold text-2xl" :style="{ backgroundColor: 'var(--color-primary)' }">S</div>
        <h1 class="text-2xl font-bold text-[var(--color-text)]">Cambiar Contraseña</h1>
        <p class="text-sm text-[var(--color-text-muted)] mt-1">Por seguridad, debes actualizar tu contraseña</p>
      </div>

      <div class="neumorphic-card p-8">
        <form @submit.prevent="submit">
          <div class="mb-5">
            <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Nueva Contraseña</label>
            <input v-model="form.password" type="password" placeholder="••••••••" required autocomplete="new-password"
              class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl py-3 px-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
            <div v-if="form.password" class="mt-3">
              <PasswordStrengthIndicator :password="form.password" :resultados="resultados" :level="level" :errores="errores" :submitted="submitted" />
            </div>
          </div>
          <div class="mb-6">
            <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Confirmar Contraseña</label>
            <input v-model="form.password_confirmation" type="password" placeholder="••••••••" required autocomplete="new-password"
              class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl py-3 px-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
          </div>
          <button type="submit" :disabled="form.processing"
            class="w-full py-3 px-6 text-white font-medium rounded-2xl transition-all duration-200 disabled:opacity-50" :style="{ backgroundColor: 'var(--color-primary)' }">
            <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2 align-middle"></span>
            Actualizar Contraseña
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.neumorphic-card { background: var(--color-surface); border-radius: 24px; box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light); }
</style>

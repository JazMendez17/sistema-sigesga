<script setup>
import { ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme'

useTheme()

const reenviando = ref(false)

const form = useForm({
  email: '',
  codigo: '',
})

const submit = () => {
  form.post(route('unlock.store'))
}

const reenviarCodigo = () => {
  if (!form.email) {
    form.setError('email', 'Ingresa tu correo primero.')
    return
  }
  reenviando.value = true
  router.post(route('unlock.reenviar'), { email: form.email }, {
    onSuccess: () => {
      reenviando.value = false
    },
    onError: () => {
      reenviando.value = false
    },
  })
}
</script>

<template>
  <Head title="Desbloquear Cuenta" />
  <div class="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-white font-bold text-2xl" :style="{ backgroundColor: 'var(--color-primary)' }">S</div>
        <h1 class="text-2xl font-bold text-[var(--color-text)]">Desbloquear Cuenta</h1>
        <p class="text-sm text-[var(--color-text-muted)] mt-1">Ingresa el código enviado a tu correo</p>
      </div>

      <div class="neumorphic-card p-8">
        <form @submit.prevent="submit">
          <div class="mb-5">
            <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Correo electrónico</label>
            <input v-model="form.email" type="email" placeholder="tu@correo.com" required autofocus
              class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl py-3 px-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-500">{{ form.errors.email }}</p>
          </div>
          <div class="mb-6">
            <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Código de 6 dígitos</label>
            <input v-model="form.codigo" type="text" placeholder="000000" maxlength="6" required
              class="w-full bg-[var(--color-bg)] text-[var(--color-text)] text-center text-2xl tracking-[0.5em] rounded-2xl py-3 px-4 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
            <p v-if="form.errors.codigo" class="mt-2 text-sm text-red-500">{{ form.errors.codigo }}</p>
          </div>
          <button type="submit" :disabled="form.processing"
            class="w-full py-3 px-6 text-white font-medium rounded-2xl transition-all duration-200 disabled:opacity-50" :style="{ backgroundColor: 'var(--color-primary)' }">
            <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2 align-middle"></span>
            Desbloquear Cuenta
          </button>
          <button type="button" @click="reenviarCodigo" :disabled="reenviando"
            class="w-full mt-3 py-3 px-6 text-sm font-medium rounded-2xl transition-all duration-200 border border-[var(--color-primary)] text-[var(--color-primary)] hover:bg-[var(--color-primary)] hover:text-white disabled:opacity-50">
            <span v-if="reenviando" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin mr-2 align-middle"></span>
            Reenviar código
          </button>
        </form>
      </div>

      <p class="text-center mt-6 text-sm text-[var(--color-text-muted)]">
        <Link :href="route('login')" class="text-[var(--color-primary)] hover:underline font-medium">Volver al inicio de sesión</Link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.neumorphic-card { background: var(--color-surface); border-radius: 24px; box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light); }
</style>

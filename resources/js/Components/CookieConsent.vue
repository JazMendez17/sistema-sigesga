<script setup>
import { ref, onMounted } from 'vue'

const COOKIE_NAME = 'sigesga_cookie_consent'
const visible = ref(false)
const mostrarConfig = ref(false)
const preferenciasAceptadas = ref(true)

function leerCookie(nombre) {
  if (typeof document === 'undefined') return null
  const prefix = `${nombre}=`
  const encontrada = document.cookie
    .split(';')
    .map((c) => c.trim())
    .find((c) => c.startsWith(prefix))
  return encontrada ? decodeURIComponent(encontrada.slice(prefix.length)) : null
}

function guardarConsentimiento(nivel, preferencias) {
  const payload = JSON.stringify({
    version: 1,
    level: nivel,
    essential: true,
    preferences: Boolean(preferencias),
    accepted_at: new Date().toISOString(),
  })

  try {
    localStorage.setItem(COOKIE_NAME, payload)
  } catch {
    // ignorar si localStorage está restringido
  }

  const secureFlag = typeof window !== 'undefined' && window.location.protocol === 'https:' ? '; Secure' : ''
  document.cookie = `${COOKIE_NAME}=${encodeURIComponent(nivel)}; Max-Age=${60 * 60 * 24 * 180}; Path=/; SameSite=Lax${secureFlag}`

  visible.value = false
  mostrarConfig.value = false
}

function aceptarTodas() {
  preferenciasAceptadas.value = true
  guardarConsentimiento('all', true)
}

function soloEsenciales() {
  preferenciasAceptadas.value = false
  guardarConsentimiento('essential', false)
}

function guardarPreferencias() {
  guardarConsentimiento(preferenciasAceptadas.value ? 'all' : 'essential', preferenciasAceptadas.value)
}

onMounted(() => {
  let guardado = leerCookie(COOKIE_NAME)
  if (!guardado) {
    try {
      guardado = localStorage.getItem(COOKIE_NAME)
    } catch {
      guardado = null
    }
  }
  if (!guardado) {
    visible.value = true
  }
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-6"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-6"
    >
      <div
        v-if="visible"
        role="dialog"
        aria-live="polite"
        aria-label="Aviso de cookies y privacidad"
        class="fixed bottom-4 left-4 right-4 z-[9990] mx-auto max-w-4xl rounded-3xl border border-gray-200/80 bg-white/95 p-5 shadow-2xl backdrop-blur-md sm:p-6"
      >
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="space-y-1.5 text-sm text-gray-700">
            <div class="flex items-center gap-2">
              <span
                class="inline-flex h-7 w-7 items-center justify-center rounded-xl text-white text-xs font-bold"
                :style="{ backgroundColor: 'var(--color-primary, #4F46E5)' }"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </span>
              <h2 class="font-bold text-gray-900">Uso de Cookies y Protección de Datos</h2>
            </div>
            <p class="leading-relaxed text-gray-600">
              Utilizamos cookies técnicas y cifradas esenciales para mantener tu sesión segura, prevenir ataques CSRF y resguardar los datos personales de usuarios, clientes y pólizas. Puedes aceptar todas las cookies o conservar únicamente las esenciales.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2 sm:flex-nowrap md:shrink-0">
            <button
              type="button"
              @click="mostrarConfig = !mostrarConfig"
              class="rounded-2xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50"
            >
              {{ mostrarConfig ? 'Ocultar detalle' : 'Configurar' }}
            </button>
            <button
              type="button"
              @click="soloEsenciales"
              class="rounded-2xl bg-gray-100 px-4 py-2.5 text-xs font-semibold text-gray-800 transition hover:bg-gray-200"
            >
              Solo esenciales
            </button>
            <button
              type="button"
              @click="aceptarTodas"
              class="rounded-2xl px-5 py-2.5 text-xs font-semibold text-white shadow-md transition hover:opacity-95"
              :style="{ backgroundColor: 'var(--color-primary, #4F46E5)' }"
            >
              Aceptar cookies
            </button>
          </div>
        </div>

        <div v-if="mostrarConfig" class="mt-4 space-y-3 border-t border-gray-200 pt-4 text-xs text-gray-600">
          <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-gray-50 p-3.5">
              <div class="flex items-center justify-between">
                <span class="font-semibold text-gray-800">Cookies esenciales de seguridad</span>
                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-[11px] font-semibold text-green-700">Siempre activas</span>
              </div>
              <p class="mt-1 leading-relaxed">
                Sesión cifrada (<code>HttpOnly</code>, <code>SameSite=Lax</code>) y token <code>XSRF-TOKEN</code> necesarios para autenticación y protección contra falsificación de solicitudes.
              </p>
            </div>
            <div class="rounded-2xl bg-gray-50 p-3.5">
              <label class="flex cursor-pointer items-center justify-between">
                <span class="font-semibold text-gray-800">Cookies de preferencias de interfaz</span>
                <input
                  v-model="preferenciasAceptadas"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
              </label>
              <p class="mt-1 leading-relaxed">
                Permiten recordar ajustes locales de visualización y experiencia dentro del panel sin compartir datos con terceros.
              </p>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
            <p class="text-[11px] text-gray-500">
              Tus datos están aislados por empresa, protegidos con cifrado de contraseñas y borrado físico bajo solicitud administrativa.
            </p>
            <button
              type="button"
              @click="guardarPreferencias"
              class="rounded-xl px-4 py-2 text-xs font-semibold text-white"
              :style="{ backgroundColor: 'var(--color-primary, #4F46E5)' }"
            >
              Guardar preferencias
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

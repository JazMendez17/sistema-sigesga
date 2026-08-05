// Composable para gestionar el estado de carga entre páginas de Inertia
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const loading = ref(false)
const loadingStartTime = ref(0)

let initialized = false

export function usePageLoading() {
  if (!initialized) {
    initialized = true

    // Muestra el indicador de carga al iniciar la navegación
    router.on('start', () => {
      loadingStartTime.value = Date.now()
      loading.value = true
    })

    // Oculta el indicador de carga con un retardo mínimo para evitar parpadeos
    router.on('finish', () => {
      const elapsed = Date.now() - loadingStartTime.value
      const delay = elapsed < 300 ? 300 - elapsed : 0
      setTimeout(() => {
        loading.value = false
      }, delay)
    })
  }

  return { loading }
}

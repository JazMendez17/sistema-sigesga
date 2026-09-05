<!--
  Componente de cálculo de rutas y peajes para cotizaciones de grúas.
  - Autocompletado de Origen/Destino con Place Autocomplete (New).
  - Mapa con polylines de cada ruta alternativa (clic = seleccionar).
  - La petición de rutas se hace SIEMPRE al backend (proxy seguro,
    Google Routes API v2 con peajes), nunca al navegador.
  - Emite el evento `seleccionada` con { distancia_km, duracion_texto,
    costo_peaje, moneda_peaje, origen_lat, origen_lng, destino_lat, destino_lng }.
-->
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  origen: { type: String, required: true },
  destino: { type: String, required: true },
  direccionesValidas: { type: Boolean, default: false },
})

const emit = defineEmits(['seleccionada'])
const apiKey = usePage().props.googleMapsKey || ''

const mapaEl = ref(null)
const cargando = ref(false)
const error = ref('')
const rutas = ref([])
const seleccionada = ref(-1)
const googleMaps = ref(null)
const mapa = ref(null)
const polylines = ref([])
const marcadores = ref([])
const busquedaRealizada = ref(false)

const COLORES = ['#4F46E5', '#059669', '#D97706', '#DC2626']
// ─── Carga perezosa del SDK de Google Maps ───────────────────────────────
let promesaCarga = null
let callbackCarga = null

function cargarGoogleMaps() {
  if (window.google?.maps) return Promise.resolve(window.google.maps)
  if (promesaCarga) return promesaCarga
  if (!apiKey) return Promise.reject(new Error('Falta GOOGLE_MAPS_FRONTEND_KEY.'))

  const promesa = new Promise((resolve, reject) => {
    const script = document.createElement('script')
    callbackCarga = `__sigesgaMapsInit_${Date.now()}`
    let temporizador = null
    const limpiar = () => {
      if (temporizador) window.clearTimeout(temporizador)
      if (callbackCarga && window[callbackCarga]) delete window[callbackCarga]
      callbackCarga = null
    }
    window[callbackCarga] = () => {
      if (!window.google?.maps) {
        limpiar()
        reject(new Error('Google Maps no se pudo inicializar.'))
        return
      }
      limpiar()
      resolve(window.google.maps)
    }
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&loading=async&callback=${callbackCarga}`
    script.onerror = () => reject(new Error('No se pudo cargar Google Maps.'))
    document.head.appendChild(script)
    temporizador = window.setTimeout(() => {
      limpiar()
      reject(new Error('Google Maps tardó demasiado en responder.'))
    }, 15000)
  })

  promesaCarga = promesa.catch((e) => {
    // Permite reintentar si el primer intento falló por configuración o red.
    promesaCarga = null
    throw e
  })
  return promesaCarga
}

// ─── Inicialización del mapa ─────────────────────────────────────────────
async function iniciar() {
  try {
    googleMaps.value = await cargarGoogleMaps()
    mapa.value = new googleMaps.value.Map(mapaEl.value, {
      center: { lat: 23.6345, lng: -102.5528 }, // centro de México
      zoom: 5,
      mapTypeId: 'roadmap',
      disableDefaultUI: true,
      zoomControl: true,
      fullscreenControl: true,
    })

  } catch (e) {
    console.error('Google Maps:', e)
    error.value = e?.message || 'No se pudo inicializar Google Maps.'
  }
}

// ─── Búsqueda de rutas (proxy al backend) ─────────────────────────────────
async function buscarRutas() {
  error.value = ''
  if (!props.direccionesValidas) {
    error.value = 'Completa Calle, Municipio, Estado y Código Postal en ambas direcciones.'
    return
  }

  cargando.value = true
  seleccionada.value = -1
  try {
    const { data } = await axios.post(route('panel.cotizaciones.rutas'), {
      origen: props.origen,
      destino: props.destino,
    })

    if (!data.ok) throw new Error(data.error || 'No se pudieron calcular las rutas.')

    rutas.value = data.rutas || []
    busquedaRealizada.value = true
    dibujarRutas()
    seleccionar(0)
  } catch (e) {
    rutas.value = []
    busquedaRealizada.value = false
    error.value = e?.response?.data?.error || e.message || 'Error al calcular rutas.'
  } finally {
    cargando.value = false
  }
}

// ─── Dibujo de polylines + marcadores + ajuste de encuadre ────────────────
function dibujarRutas() {
  limpiarMapa()
  if (!googleMaps.value || !mapa.value) return

  const limites = new googleMaps.value.LatLngBounds()
  const gm = googleMaps.value

  rutas.value.forEach((ruta, i) => {
    const trazo = new gm.Polyline({
      path: ruta.coordenadas,
      strokeColor: COLORES[i % COLORES.length],
      strokeOpacity: 0.9,
      strokeWeight: i === seleccionada.value ? 7 : 4,
      map: mapa.value,
      zIndex: i === seleccionada.value ? 20 : 10,
    })
    trazo.addListener('click', () => seleccionar(i))
    polylines.value.push(trazo)

    ruta.coordenadas.forEach((punto) => limites.extend(punto))
  })

  const puntos = rutas.value[0]?.coordenadas || []
  if (puntos.length > 1) {
    marcadores.value.push(
      new gm.Marker({ position: puntos[0], map: mapa.value, label: 'A', title: 'Origen' }),
      new gm.Marker({ position: puntos[puntos.length - 1], map: mapa.value, label: 'B', title: 'Destino' }),
    )
    limites.extend(puntos[0])
    limites.extend(puntos[puntos.length - 1])
  }

  mapa.value.fitBounds(limites)
}

// ─── Selección visual de ruta (tarjeta o clic en el mapa) ─────────────────
function seleccionar(indice) {
  if (indice < 0 || indice >= rutas.value.length) return
  seleccionada.value = indice

  polylines.value.forEach((trazo, i) => {
    trazo.setOptions({
      strokeWeight: i === indice ? 7 : 3,
      strokeOpacity: i === indice ? 0.95 : 0.3,
      zIndex: i === indice ? 20 : 10,
    })
  })

  const r = rutas.value[indice]
  emit('seleccionada', {
    distancia_km: r.distancia_km,
    duracion_texto: r.duracion_texto,
    costo_peaje: r.costo_peaje,
    moneda_peaje: r.moneda_peaje,
  })
}

function formatearPeaje(ruta) {
  if (!ruta.costo_peaje || ruta.costo_peaje <= 0) return 'Sin casetas estimadas'
  return new Intl.NumberFormat('es-MX', { style: 'currency', currency: ruta.moneda_peaje || 'MXN' }).format(ruta.costo_peaje)
}

function limpiarMapa() {
  polylines.value.forEach((p) => p.setMap(null))
  marcadores.value.forEach((m) => m.setMap(null))
  polylines.value = []
  marcadores.value = []
}

onMounted(iniciar)

onUnmounted(() => {
  limpiarMapa()
  if (mapa.value && googleMaps.value) {
    googleMaps.value.event.clearInstanceListeners(mapa.value)
  }
})
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-3">
      <NeumorphicButton type="button" :loading="cargando" @click="buscarRutas">
        Calcular Rutas y Casetas
      </NeumorphicButton>
      <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
    </div>

    <!-- Opciones de ruta -->
    <div v-if="rutas.length" class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <button
        v-for="(ruta, i) in rutas"
        :key="ruta.indice"
        type="button"
        class="text-left rounded-2xl p-4 transition-all duration-200 cursor-pointer"
        :class="seleccionada === i
          ? 'bg-[var(--color-primary)] text-white shadow-[0_10px_20px_-5px_color-mix(in_srgb,var(--color-primary)_50%,transparent)] scale-[1.02]'
          : 'bg-[var(--color-surface)] shadow-[6px_6px_12px_var(--neumorphic-dark),-6px_-6px_12px_var(--neumorphic-light)] hover:scale-[1.01]'"
        @click="seleccionar(i)"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-bold">{{ seleccionada === i ? '✓ ' : '' }}Ruta {{ i + 1 }}</span>
          <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: COLORES[i % COLORES.length] }"></span>
        </div>
        <div class="text-xs opacity-90 grid grid-cols-2 gap-1">
          <span>Distancia:</span><span class="font-semibold text-right">{{ ruta.distancia_km.toLocaleString('es-MX') }} km</span>
          <span>Tiempo:</span><span class="font-semibold text-right">{{ ruta.duracion_texto }}</span>
          <span class="col-span-2 border-t mt-1 pt-1">Casetas/Peajes</span>
          <span class="col-span-2 text-sm font-bold text-right">{{ formatearPeaje(ruta) }}</span>
        </div>
      </button>
    </div>

    <!-- Mapa -->
    <div
      ref="mapaEl"
      class="w-full h-[420px] rounded-3xl overflow-hidden shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]"
      :class="{ 'opacity-60': busquedaRealizada && seleccionada < 0 }"
    ></div>
    <p v-if="busquedaRealizada && seleccionada < 0" class="text-xs text-[var(--color-text-muted)]">
      Selecciona una ruta haciendo clic en una tarjeta o en la línea del mapa.
    </p>
  </div>
</template>


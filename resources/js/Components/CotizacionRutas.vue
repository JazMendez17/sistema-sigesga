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
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  apiKey: { type: String, default: '' },
  country: { type: String, default: 'MX' },
  language: { type: String, default: 'es' },
})

const emit = defineEmits(['seleccionada'])

const page = usePage()
const apiKey = props.apiKey || page.props.googleMapsKey || ''

const origenAutocompleteEl = ref(null)
const destinoAutocompleteEl = ref(null)
const mapaEl = ref(null)

const origen = ref(null)   // { direccion, lat, lng }
const destino = ref(null)
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
let autocompleteOrigen = null
let autocompleteDestino = null

// ─── Carga perezosa del SDK de Google Maps (con callback global) ──────────
let promesaCarga = null
let callbackCarga = null
let authFailureHandler = null
let authFailureOriginal = null

function cargarGoogleMaps() {
  if (window.google?.maps) return Promise.resolve(window.google.maps)
  if (promesaCarga) return promesaCarga
  if (!apiKey) {
    return Promise.reject(new Error('Falta la API Key del navegador (GOOGLE_MAPS_FRONTEND_KEY).'))
  }
  const promesa = new Promise((resolve, reject) => {
    let finalizada = false
    let temporizador = null
    let limpiarGlobales = () => {}

    const fallar = (mensaje) => {
      if (finalizada) return
      finalizada = true
      limpiarGlobales()
      reject(new Error(mensaje))
    }

    const resolver = () => {
      if (finalizada) return
      if (!window.google?.maps) {
        fallar('Google Maps se cargó sin exponer el objeto de mapas.')
        return
      }
      finalizada = true
      limpiarGlobales()
      resolve(window.google.maps)
    }

    const script = document.createElement('script')
    callbackCarga = `__sigesgaGmapsInit_${Date.now()}`
    window[callbackCarga] = resolver

    authFailureOriginal = window.gm_authFailure
    authFailureHandler = () => fallar('Google rechazó la llave del navegador. Revisa Maps JavaScript API, Places API y las restricciones de HTTP referrers.')
    window.gm_authFailure = authFailureHandler

    limpiarGlobales = () => {
      if (temporizador) window.clearTimeout(temporizador)
      if (callbackCarga && window[callbackCarga] === resolver) delete window[callbackCarga]
      if (window.gm_authFailure === authFailureHandler) {
        if (authFailureOriginal) window.gm_authFailure = authFailureOriginal
        else delete window.gm_authFailure
      }
      callbackCarga = null
      authFailureHandler = null
      authFailureOriginal = null
    }

    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&loading=async&libraries=places&language=${encodeURIComponent(props.language)}&region=${encodeURIComponent(props.country)}&callback=${callbackCarga}`
    script.onerror = () => fallar('No se pudo cargar Google Maps. Revisa la llave del navegador y su restricción de referrers.')
    document.head.appendChild(script)

    temporizador = window.setTimeout(() => fallar('Google Maps tardó demasiado en responder. Revisa la conexión y la configuración de la API.'), 15000)
  })

  promesaCarga = promesa.catch((e) => {
    // Permite reintentar si el primer intento falló por configuración o red.
    promesaCarga = null
    throw e
  })
  return promesaCarga
}

// ─── Inicialización del mapa y los autocompletados ────────────────────────
async function iniciar() {
  try {
    googleMaps.value = await cargarGoogleMaps()
    await nextTick()

    mapa.value = new googleMaps.value.Map(mapaEl.value, {
      center: { lat: 23.6345, lng: -102.5528 }, // centro de México
      zoom: 5,
      mapTypeId: 'roadmap',
      disableDefaultUI: true,
      zoomControl: true,
      fullscreenControl: true,
    })

    if (typeof googleMaps.value.importLibrary !== 'function') {
      throw new Error('La versión cargada de Google Maps no soporta Place Autocomplete (New).')
    }

    const { PlaceAutocompleteElement } = await googleMaps.value.importLibrary('places')
    if (!PlaceAutocompleteElement) {
      throw new Error('Places API no está disponible. Habilita Places API en el mismo proyecto de Google Cloud.')
    }

    autocompleteOrigen = crearAutocompletado(
      PlaceAutocompleteElement,
      origenAutocompleteEl.value,
      'origen',
      'Ej. Av. Insurgentes 300, CDMX',
    )
    autocompleteDestino = crearAutocompletado(
      PlaceAutocompleteElement,
      destinoAutocompleteEl.value,
      'destino',
      'Ej. Carr. México-Querétaro km 120',
    )
  } catch (e) {
    console.error('Google Maps:', e)
    error.value = e?.message || 'No se pudo inicializar Google Maps.'
  }
}

function crearAutocompletado(PlaceAutocompleteElement, contenedor, tipo, placeholder) {
  const autocomplete = new PlaceAutocompleteElement({
    includedRegionCodes: [props.country.toLowerCase()],
    requestedLanguage: props.language,
    requestedRegion: props.country.toLowerCase(),
  })

  autocomplete.placeholder = placeholder
  autocomplete.description = `Dirección de ${tipo}`
  autocomplete.style.display = 'block'
  autocomplete.style.width = '100%'
  autocomplete.style.backgroundColor = 'var(--color-bg)'
  autocomplete.style.border = '0'
  autocomplete.style.borderRadius = '1rem'
  autocomplete.style.color = 'var(--color-text)'
  autocomplete.style.fontFamily = 'inherit'
  contenedor.replaceChildren(autocomplete)

  autocomplete.addEventListener('input', () => {
    if (tipo === 'origen') origen.value = null
    else destino.value = null
  })

  autocomplete.addEventListener('gmp-error', () => {
    error.value = 'Places rechazó la consulta. Revisa que Places API esté habilitada y que el dominio actual esté permitido en la llave del navegador.'
  })

  autocomplete.addEventListener('gmp-select', async (evento) => {
    try {
      const lugar = evento.placePrediction?.toPlace()
      if (!lugar) throw new Error('No se recibió el lugar seleccionado.')

      await lugar.fetchFields({ fields: ['displayName', 'formattedAddress', 'location'] })
      if (!lugar.location) throw new Error('El lugar seleccionado no tiene coordenadas.')

      const nombre = typeof lugar.displayName === 'string' ? lugar.displayName : lugar.displayName?.text
      const punto = {
        direccion: lugar.formattedAddress || nombre || '',
        lat: lugar.location.lat(),
        lng: lugar.location.lng(),
      }

      if (tipo === 'origen') origen.value = punto
      else destino.value = punto
      error.value = ''
    } catch (e) {
      console.error(`Google Places (${tipo}):`, e)
      error.value = e?.message || `No se pudo obtener el ${tipo} seleccionado.`
    }
  })

  return autocomplete
}

// ─── Búsqueda de rutas (proxy al backend) ─────────────────────────────────
async function buscarRutas() {
  error.value = ''
  if (!origen.value || !destino.value) {
    error.value = 'Selecciona primero dirección de origen y destino con el autocompletado.'
    return
  }
  if (origen.value.lat === destino.value.lat && origen.value.lng === destino.value.lng) {
    error.value = 'El origen y el destino son el mismo punto.'
    return
  }

  cargando.value = true
  seleccionada.value = -1
  try {
    const { data } = await axios.post(route('panel.cotizaciones.rutas'), {
      origen_lat: origen.value.lat,
      origen_lng: origen.value.lng,
      destino_lat: destino.value.lat,
      destino_lng: destino.value.lng,
    })

    if (!data.ok) throw new Error(data.error || 'No se pudieron calcular las rutas.')

    rutas.value = data.rutas || []
    busquedaRealizada.value = true
    dibujarRutas()
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

  marcadores.value.push(
    new gm.Marker({ position: { lat: origen.value.lat, lng: origen.value.lng }, map: mapa.value, label: 'A', title: 'Origen' }),
    new gm.Marker({ position: { lat: destino.value.lat, lng: destino.value.lng }, map: mapa.value, label: 'B', title: 'Destino' }),
  )
  limites.extend(marcadores.value[0].getPosition())
  limites.extend(marcadores.value[1].getPosition())

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
    origen_lat: origen.value.lat,
    origen_lng: origen.value.lng,
    destino_lat: destino.value.lat,
    destino_lng: destino.value.lng,
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

function limpiarAutocompletados() {
  autocompleteOrigen?.remove()
  autocompleteDestino?.remove()
  autocompleteOrigen = null
  autocompleteDestino = null
}

onMounted(iniciar)

onUnmounted(() => {
  limpiarMapa()
  limpiarAutocompletados()
  if (mapa.value && googleMaps.value) {
    googleMaps.value.event.clearInstanceListeners(mapa.value)
  }
})
</script>

<template>
  <div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-2">Dirección de Origen (autocompletado)</label>
        <div ref="origenAutocompleteEl" class="google-autocomplete"></div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Dirección de Destino (autocompletado)</label>
        <div ref="destinoAutocompleteEl" class="google-autocomplete"></div>
      </div>
    </div>

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

<style scoped>
.google-autocomplete {
  min-height: 48px;
}

.google-autocomplete > *::part(input) {
  width: 100%;
  min-height: 48px;
  padding: 0.75rem 1rem;
  border: 0;
  border-radius: 1rem;
  color: var(--color-text);
  background: var(--color-bg);
  box-shadow: inset 6px 6px 12px var(--neumorphic-dark), inset -6px -6px 12px var(--neumorphic-light);
  outline: none;
}

.google-autocomplete > *::part(input):focus {
  box-shadow: inset 6px 6px 12px var(--neumorphic-dark), inset -6px -6px 12px var(--neumorphic-light), 0 0 0 2px var(--color-primary);
}
</style>

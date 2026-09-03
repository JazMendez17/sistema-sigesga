<script setup>
import { ref, computed, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import CotizacionRutas from '@/Components/CotizacionRutas.vue'
import { useFormValidation } from '@/Composables/useFormValidation'
import { ESTADOS, municipiosPorEstado } from '@/Data/estadosMunicipios'
import { showValidationErrors } from '@/stores/notification'

const page = usePage()
const clientes = computed(() => page.props.clientes ?? [])
const tiposServicio = computed(() => page.props.tiposServicio ?? [])
const submitted = ref(false)
const cargandoTarifa = ref(false)
const tarifaAplicada = ref(null)
const municipiosOrigen = ref([])
const municipiosDestino = ref([])
const coloniasOrigen = ref([])
const coloniasDestino = ref([])
const cargandoColoniasOrigen = ref(false)
const cargandoColoniasDestino = ref(false)
const casetasPeaje = ref(0)
const monedaCasetas = ref('MXN')

// Aplica la ruta seleccionada en el mapa al formulario de cotización:
// distancia (alimenta el cálculo por km) y coordenadas (se guardan en la cotización).
function aplicarRuta(r) {
  form.distancia_km = r.distancia_km
  form.origen_lat = r.origen_lat
  form.origen_lng = r.origen_lng
  form.destino_lat = r.destino_lat
  form.destino_lng = r.destino_lng
  casetasPeaje.value = r.costo_peaje || 0
  monedaCasetas.value = r.moneda_peaje || 'MXN'
  calcularTotal()
}

function formatearCasetas() {
  return new Intl.NumberFormat('es-MX', { style: 'currency', currency: monedaCasetas.value }).format(casetasPeaje.value)
}

function actualizarMunicipiosOrigen() {
  municipiosOrigen.value = municipiosPorEstado(form.origen.estado)
  if (!municipiosOrigen.value.includes(form.origen.municipio_alcaldia)) form.origen.municipio_alcaldia = ''
}
function actualizarMunicipiosDestino() {
  municipiosDestino.value = municipiosPorEstado(form.destino.estado)
  if (!municipiosDestino.value.includes(form.destino.municipio_alcaldia)) form.destino.municipio_alcaldia = ''
}

async function buscarColoniasOrigen() {
  const cp = form.origen.codigo_postal?.trim()
  if (!cp || cp.length < 5) { coloniasOrigen.value = []; return }
  cargandoColoniasOrigen.value = true
  try {
    const { data } = await axios.get(`/panel/api/colonias/${cp}`)
    coloniasOrigen.value = data
    if (data.length === 1) {
      form.origen.colonia = data[0].colonia
      form.origen.estado = data[0].estado
      form.origen.municipio_alcaldia = data[0].municipio
      actualizarMunicipiosOrigen()
    } else if (data.length > 1) {
      form.origen.colonia = ''
      form.origen.estado = data[0].estado
      form.origen.municipio_alcaldia = data[0].municipio
      actualizarMunicipiosOrigen()
    } else {
      form.origen.colonia = ''
    }
  } catch (e) {
    coloniasOrigen.value = []
  } finally {
    cargandoColoniasOrigen.value = false
  }
}

async function buscarColoniasDestino() {
  const cp = form.destino.codigo_postal?.trim()
  if (!cp || cp.length < 5) { coloniasDestino.value = []; return }
  cargandoColoniasDestino.value = true
  try {
    const { data } = await axios.get(`/panel/api/colonias/${cp}`)
    coloniasDestino.value = data
    if (data.length === 1) {
      form.destino.colonia = data[0].colonia
      form.destino.estado = data[0].estado
      form.destino.municipio_alcaldia = data[0].municipio
      actualizarMunicipiosDestino()
    } else if (data.length > 1) {
      form.destino.colonia = ''
      form.destino.estado = data[0].estado
      form.destino.municipio_alcaldia = data[0].municipio
      actualizarMunicipiosDestino()
    } else {
      form.destino.colonia = ''
    }
  } catch (e) {
    coloniasDestino.value = []
  } finally {
    cargandoColoniasDestino.value = false
  }
}

function seleccionarColoniaOrigen(colonia) {
  form.origen.colonia = colonia.colonia
  form.origen.estado = colonia.estado
  form.origen.municipio_alcaldia = colonia.municipio
  actualizarMunicipiosOrigen()
}

function seleccionarColoniaDestino(colonia) {
  form.destino.colonia = colonia.colonia
  form.destino.estado = colonia.estado
  form.destino.municipio_alcaldia = colonia.municipio
  actualizarMunicipiosDestino()
}

const form = useForm({
  cliente_id: '',
  tipo_servicio_id: '',
  origen: { calle:'',numero_exterior:'',numero_interior:'',colonia:'',codigo_postal:'',municipio_alcaldia:'',ciudad:'',estado:'',pais:'México',referencias:'' },
  destino: { calle:'',numero_exterior:'',numero_interior:'',colonia:'',codigo_postal:'',municipio_alcaldia:'',ciudad:'',estado:'',pais:'México',referencias:'' },
  distancia_km: '',
  costo_total: '',
  costo_banderazo: '',
  km_incluidos: '',
  costo_km_extra: '',
  cubre_casetas: false,
  descuento_pct: '',
  convenio_id: '',
  observaciones: '',
})

// Auto-llenado al cambiar cliente o tipo de servicio
watch([() => form.cliente_id, () => form.tipo_servicio_id], async ([clienteId, servicioId]) => {
  if (!clienteId || !servicioId) return
  cargandoTarifa.value = true
  tarifaAplicada.value = null
  try {
    const { data } = await axios.get('/panel/api/cotizacion/tarifa', { params: { cliente_id: clienteId, tipo_servicio_id: servicioId } })
    if (data) {
      tarifaAplicada.value = data
      form.costo_banderazo = data.banderazo || ''
      form.km_incluidos = data.km_incluidos || ''
      form.costo_km_extra = data.costo_km_extra || ''
      form.cubre_casetas = data.cubre_casetas || false
      form.descuento_pct = data.descuento_pct || ''
      form.convenio_id = data.convenio_id || ''
      calcularTotal()
    }
  } catch (e) {
    console.error('Error al obtener tarifa:', e)
  } finally {
    cargandoTarifa.value = false
  }
})

function calcularTotal() {
  const banderazo = parseFloat(form.costo_banderazo) || 0
  const km = parseFloat(form.distancia_km) || 0
  const kmIncluidos = parseFloat(form.km_incluidos) || 0
  const kmExcedentes = Math.max(0, km - kmIncluidos)
  const costoKm = parseFloat(form.costo_km_extra) || 0
  const costoKmTotal = kmExcedentes * costoKm
  const descuentoPct = parseFloat(form.descuento_pct) || 0
  const subtotalBruto = banderazo + costoKmTotal
  const montoDescuento = subtotalBruto * (descuentoPct / 100)
  const subtotalNeto = subtotalBruto - montoDescuento
  const iva = subtotalNeto * 0.16
  // Casetas: si la empresa las cubre (cubre_casetas) no se cargan al cliente.
  const casetas = form.cubre_casetas ? 0 : casetasPeaje.value
  const total = Math.max(banderazo, subtotalNeto) + iva + casetas
  form.costo_total = total.toFixed(2)
}

function calculosDetalle() {
  const banderazo = parseFloat(form.costo_banderazo) || 0
  const km = parseFloat(form.distancia_km) || 0
  const kmIncluidos = parseFloat(form.km_incluidos) || 0
  const kmExcedentes = Math.max(0, km - kmIncluidos)
  const costoKm = parseFloat(form.costo_km_extra) || 0
  const costoKmTotal = kmExcedentes * costoKm
  const descuentoPct = parseFloat(form.descuento_pct) || 0
  const subtotalBruto = banderazo + costoKmTotal
  const montoDescuento = subtotalBruto * (descuentoPct / 100)
  const subtotalNeto = Math.max(banderazo, subtotalBruto - montoDescuento)
  const iva = subtotalNeto * 0.16
  const casetas = form.cubre_casetas ? 0 : casetasPeaje.value
  return {
    banderazo, kmExcedentes, costoKm, costoKmTotal,
    descuentoPct, montoDescuento, subtotalBruto,
    subtotalNeto, iva, casetas, total: subtotalNeto + iva + casetas
  }
}

watch(() => form.distancia_km, () => calcularTotal())

const rules = {
  cliente_id: ['required'],
  tipo_servicio_id: ['required'],
}
const val = useFormValidation(form, rules)
const mostrandoPreview = ref(false)

function vistaPrevia() {
  submitted.value = true
  if (!val.validate()) { const e = Object.values(val.clientErrors).filter(Boolean); if (e.length) showValidationErrors(e); return }
  if (!form.origen.calle) { showValidationErrors(['La dirección de origen es obligatoria.']); return }
  mostrandoPreview.value = true
}

function doSubmit() {
  if (!mostrandoPreview.value) { vistaPrevia(); return }
  const build = d => [d.calle,d.numero_exterior,d.numero_interior,d.colonia,d.codigo_postal,d.municipio_alcaldia,d.ciudad,d.estado,d.pais].filter(Boolean).join(', ') + (d.referencias?' ('+d.referencias+')':'')
  form.transform(data => {
    const calc = calculosDetalle()
    return {
      ...data,
      origen_direccion: build(data.origen),
      destino_direccion: build(data.destino),
      km_excedente: calc.kmExcedentes,
      subtotal: calc.subtotalBruto,
      monto_descuento: calc.montoDescuento,
      descuento_pct: calc.descuentoPct,
      monto_iva: calc.iva,
      km_incluidos: parseFloat(data.km_incluidos) || 0,
      costo_km: data.costo_km_extra,
      costo_total: calc.total,
    }
  }).post(route('panel.cotizaciones.store'), { onSuccess: () => { form.reset(); mostrandoPreview.value = false } })
}

function clienteNombre() {
  const c = clientes.value.find(c => c.id == form.cliente_id)
  return c ? [c.nombre, c.apellido_paterno, c.apellido_materno].filter(Boolean).join(' ') : '—'
}

function servicioNombre() {
  const ts = tiposServicio.value.find(t => t.id == form.tipo_servicio_id)
  return ts?.nombre || '—'
}
function aseguradoraNombre() {
  if (tarifaAplicada.value?.origen === 'convenio') return tarifaAplicada.value.convenio_nombre || '—'
  return 'Particular (Tarifa Pública)'
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Nueva Cotización</h1>
        <p class="text-sm text-gray-500 mt-1">Llena los datos para generar una nueva cotización</p>
      </div>
      <div class="neumorphic-card p-6 max-w-3xl">
        <form @submit.prevent="doSubmit" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Cliente</label>
              <select v-model="form.cliente_id" @change="val.handleInput('cliente_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar cliente...</option>
                <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }} {{ c.apellido_paterno??'' }} {{ c.apellido_materno??'' }}</option>
              </select>
              <p v-if="val.getError('cliente_id')" class="text-sm text-red-500 mt-1">{{ val.getError('cliente_id') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Servicio</label>
              <select v-model="form.tipo_servicio_id" @change="val.handleInput('tipo_servicio_id')" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar tipo...</option>
                <option v-for="ts in tiposServicio" :key="ts.id" :value="ts.id">{{ ts.nombre }}</option>
              </select>
              <p v-if="val.getError('tipo_servicio_id')" class="text-sm text-red-500 mt-1">{{ val.getError('tipo_servicio_id') }}</p>
            </div>
          </div>

          <!-- Origen -->
          <div class="border-t border-gray-200 pt-4"><p class="text-sm font-medium text-gray-600 mb-3">Dirección de Origen</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <NeumorphicInput v-model="form.origen.calle" label="Calle" placeholder="Av. Reforma" />
              <NeumorphicInput v-model="form.origen.numero_exterior" label="Núm. Exterior" placeholder="123" />
              <NeumorphicInput v-model="form.origen.numero_interior" label="Núm. Interior" placeholder="Opcional" />
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Código Postal</label>
                <input v-model="form.origen.codigo_postal" @input="buscarColoniasOrigen" maxlength="5" placeholder="06600" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Colonia</label>
                <select v-if="coloniasOrigen.length > 1" v-model="form.origen.colonia" @change="seleccionarColoniaOrigen(coloniasOrigen.find(c => c.colonia === form.origen.colonia))" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                  <option value="">Seleccionar colonia...</option>
                  <option v-for="c in coloniasOrigen" :key="c.colonia" :value="c.colonia">{{ c.colonia }}</option>
                </select>
                <input v-else v-model="form.origen.colonia" placeholder="Colonia" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                <p v-if="cargandoColoniasOrigen" class="text-xs text-gray-400 mt-1">Buscando colonias...</p>
                <p v-if="form.origen.codigo_postal && coloniasOrigen.length === 0 && !cargandoColoniasOrigen && form.origen.codigo_postal.length === 5" class="text-xs text-orange-500 mt-1">Sin colonias para este CP</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Estado</label>
                <select v-model="form.origen.estado" @change="actualizarMunicipiosOrigen()" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                  <option value="">Seleccionar estado...</option>
                  <option v-for="e in ESTADOS" :key="e" :value="e">{{ e }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Municipio / Alcaldía</label>
                <select v-model="form.origen.municipio_alcaldia" :disabled="!form.origen.estado" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:opacity-50">
                  <option value="">Seleccionar municipio...</option>
                  <option v-for="m in municipiosOrigen" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
              <NeumorphicInput v-model="form.origen.ciudad" label="Localidad" placeholder="CDMX" />
              <NeumorphicInput v-model="form.origen.pais" label="País" placeholder="México" />
              <div class="md:col-span-3"><NeumorphicInput v-model="form.origen.referencias" label="Referencias" placeholder="Entre calles X y Y" /></div>
            </div>
          </div>

          <!-- Destino -->
          <div class="border-t border-gray-200 pt-4"><p class="text-sm font-medium text-gray-600 mb-3">Dirección de Destino</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <NeumorphicInput v-model="form.destino.calle" label="Calle" placeholder="Av. Reforma" />
              <NeumorphicInput v-model="form.destino.numero_exterior" label="Núm. Exterior" placeholder="123" />
              <NeumorphicInput v-model="form.destino.numero_interior" label="Núm. Interior" placeholder="Opcional" />
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Código Postal</label>
                <input v-model="form.destino.codigo_postal" @input="buscarColoniasDestino" maxlength="5" placeholder="06600" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Colonia</label>
                <select v-if="coloniasDestino.length > 1" v-model="form.destino.colonia" @change="seleccionarColoniaDestino(coloniasDestino.find(c => c.colonia === form.destino.colonia))" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                  <option value="">Seleccionar colonia...</option>
                  <option v-for="c in coloniasDestino" :key="c.colonia" :value="c.colonia">{{ c.colonia }}</option>
                </select>
                <input v-else v-model="form.destino.colonia" placeholder="Colonia" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                <p v-if="cargandoColoniasDestino" class="text-xs text-gray-400 mt-1">Buscando colonias...</p>
                <p v-if="form.destino.codigo_postal && coloniasDestino.length === 0 && !cargandoColoniasDestino && form.destino.codigo_postal.length === 5" class="text-xs text-orange-500 mt-1">Sin colonias para este CP</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Estado</label>
                <select v-model="form.destino.estado" @change="actualizarMunicipiosDestino()" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                  <option value="">Seleccionar estado...</option>
                  <option v-for="e in ESTADOS" :key="e" :value="e">{{ e }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Municipio / Alcaldía</label>
                <select v-model="form.destino.municipio_alcaldia" :disabled="!form.destino.estado" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:opacity-50">
                  <option value="">Seleccionar municipio...</option>
                  <option v-for="m in municipiosDestino" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
              <NeumorphicInput v-model="form.destino.ciudad" label="Localidad" placeholder="CDMX" />
              <NeumorphicInput v-model="form.destino.pais" label="País" placeholder="México" />
              <div class="md:col-span-3"><NeumorphicInput v-model="form.destino.referencias" label="Referencias" placeholder="Entre calles X y Y" /></div>
            </div>
          </div>

          <!-- Cálculo de rutas y peajes (Google Maps) -->
          <div class="border-t border-gray-200 pt-4">
            <p class="text-sm font-medium text-gray-600 mb-3">Ruta y Peajes (Google Maps)</p>
            <p class="text-xs text-gray-400 mb-3">Selecciona origen y destino con el autocompletado, calcula las rutas y elige una opción. La distancia se aplica automáticamente a la cotización.</p>
            <CotizacionRutas @seleccionada="aplicarRuta" />
            <p v-if="casetasPeaje > 0" class="mt-3 text-xs font-medium" :class="form.cubre_casetas ? 'text-gray-400' : 'text-orange-600'">
              Casetas estimadas de la ruta seleccionada: {{ formatearCasetas() }}
              <template v-if="form.cubre_casetas">(incluidas por la empresa — no se cargan al cliente)</template>
              <template v-else>(se suman al total de la cotización)</template>
            </p>
          </div>

          <!-- Info de tarifa aplicada -->
          <div v-if="tarifaAplicada" class="rounded-2xl bg-green-50 border border-green-200 p-4">
            <p class="text-sm text-green-700 font-medium">
              {{ tarifaAplicada.origen === 'convenio' ? 'Convenio: ' + tarifaAplicada.convenio_nombre : 'Tarifa Pública' }}
            </p>
          </div>
          <div v-if="cargandoTarifa" class="text-sm text-gray-400">Cargando tarifa...</div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <NeumorphicInput v-model="form.costo_banderazo" label="Banderazo ($)" type="number" step="0.01" placeholder="Auto" />
            <NeumorphicInput v-model="form.km_incluidos" label="KM Incluidos" type="number" placeholder="Auto" />
            <NeumorphicInput v-model="form.costo_km_extra" label="Costo KM Extra ($)" type="number" step="0.01" placeholder="Auto" />
            <NeumorphicInput v-model="form.descuento_pct" label="Descuento (%)" type="number" step="0.01" placeholder="Auto" />
            <NeumorphicInput v-model="form.distancia_km" label="Distancia (km)" type="number" step="0.01" placeholder="0" />
            <NeumorphicInput v-model="form.costo_total" label="Costo Total Estimado ($)" type="number" step="0.01" placeholder="0.00" />
            <label class="flex items-center gap-2 cursor-pointer self-end pb-3">
              <input type="checkbox" v-model="form.cubre_casetas" class="w-5 h-5 rounded-md bg-[#E8EDF2] shadow-[inset_3px_3px_6px_#d0d5da,inset_-3px_-3px_6px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 appearance-none checked:bg-[#4F46E5]" />
              <span class="text-sm font-medium text-gray-600">Cubre Casetas</span>
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Observaciones</label>
            <textarea v-model="form.observaciones" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300" rows="3" placeholder="Notas adicionales..."></textarea>
          </div>
          <!-- Vista Previa -->
          <div v-if="mostrandoPreview" class="border-t border-gray-200 pt-4">
            <p class="text-sm font-medium text-gray-600 mb-3">Vista Previa de la Cotización</p>
            <div class="rounded-2xl bg-white border border-gray-200 p-6 space-y-2 text-sm shadow-sm">
              <div class="flex justify-between"><span class="text-gray-500">Cliente:</span><span class="font-medium">{{ clienteNombre() }}</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Servicio:</span><span class="font-medium">{{ servicioNombre() }}</span></div>
              <div v-if="tarifaAplicada" class="flex justify-between"><span class="text-gray-500">{{ tarifaAplicada.origen === 'convenio' ? 'Convenio:' : 'Tarifa:' }}</span><span class="font-medium">{{ aseguradoraNombre() }}</span></div>
              <div><p class="text-xs text-gray-400 uppercase mb-1">Origen</p><p class="text-sm">{{ form.origen.calle }} {{ form.origen.numero_exterior }}, {{ form.origen.colonia }}, {{ form.origen.municipio_alcaldia }}, {{ form.origen.ciudad }}, {{ form.origen.estado }}</p></div>
              <div><p class="text-xs text-gray-400 uppercase mb-1">Destino</p><p class="text-sm">{{ form.destino.calle }} {{ form.destino.numero_exterior }}, {{ form.destino.colonia }}, {{ form.destino.municipio_alcaldia }}, {{ form.destino.ciudad }}, {{ form.destino.estado }}</p></div>
              <div class="border-t my-2"></div>
              <div class="flex justify-between"><span class="text-gray-500">Banderazo Base:</span><span class="font-medium">${{ calculosDetalle().banderazo.toFixed(2) }}</span></div>
              <div v-if="calculosDetalle().kmExcedentes > 0" class="flex justify-between"><span class="text-gray-500">Kilometraje Excedente ({{ calculosDetalle().kmExcedentes }} km × ${{ calculosDetalle().costoKm.toFixed(2) }}/km):</span><span class="font-medium text-orange-600">+${{ calculosDetalle().costoKmTotal.toFixed(2) }}</span></div>
              <div v-else class="flex justify-between"><span class="text-gray-500">Kilometraje ({{ form.distancia_km || '0' }} km dentro de {{ form.km_incluidos || '0' }} km incluidos):</span><span class="font-medium text-green-600">$0.00</span></div>
              <div class="flex justify-between font-semibold border-t pt-1"><span class="text-gray-700">Subtotal Bruto:</span><span>${{ calculosDetalle().subtotalBruto.toFixed(2) }}</span></div>
              <div v-if="calculosDetalle().descuentoPct > 0" class="flex justify-between"><span class="text-gray-500">Descuento ({{ calculosDetalle().descuentoPct }}%):</span><span class="font-medium text-red-600">-${{ calculosDetalle().montoDescuento.toFixed(2) }}</span></div>
              <div class="flex justify-between font-semibold"><span class="text-gray-700">Subtotal Neto:</span><span>${{ calculosDetalle().subtotalNeto.toFixed(2) }}</span></div>
              <div class="flex justify-between"><span class="text-gray-500">IVA (16%):</span><span class="font-medium">+${{ calculosDetalle().iva.toFixed(2) }}</span></div>
              <div v-if="calculosDetalle().casetas > 0" class="flex justify-between"><span class="text-gray-500">Peajes / Casetas ({{ formatearCasetas() }}):</span><span class="font-medium text-orange-600">+${{ calculosDetalle().casetas.toFixed(2) }}</span></div>
              <div class="border-t pt-2 flex justify-between font-bold text-base"><span class="text-gray-800">TOTAL FINAL:</span><span class="text-lg">${{ calculosDetalle().total.toFixed(2) }}</span></div>
              <div v-if="form.cubre_casetas" class="text-xs text-gray-400 mt-1">* Incluye cobertura de casetas/peaje</div>
              <div v-if="form.observaciones" class="border-t pt-2"><span class="text-gray-500 text-xs">Obs:</span> {{ form.observaciones }}</div>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <template v-if="!mostrandoPreview">
              <NeumorphicButton @click="vistaPrevia" type="button">Vista Previa</NeumorphicButton>
            </template>
            <template v-else>
              <NeumorphicButton type="submit" :loading="form.processing">Confirmar y Guardar</NeumorphicButton>
              <NeumorphicButton variant="secondary" @click="mostrandoPreview = false" type="button">Editar</NeumorphicButton>
            </template>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.cotizaciones.index'))">Cancelar</NeumorphicButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: #EEF2F7; border-radius: 24px; box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff; }
</style>

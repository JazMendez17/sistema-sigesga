<script setup>
import { ref, computed } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  cotizacion: Object,
})

const page = usePage()
const operadores = computed(() => page.props.operadores ?? [])
const unidades = computed(() => page.props.unidades ?? [])
const mostrarModal = ref(false)

const form = useForm({
  operador_id: '',
  unidad_id: '',
})

const anioActual = new Date().getFullYear()
const meses = Array.from({ length: 12 }, (_, i) => ({
  value: i + 1,
  label: String(i + 1).padStart(2, '0'),
}))
const anios = Array.from({ length: 15 }, (_, i) => anioActual + i)

const pagoForm = useForm({
  tarjeta_titular: '',
  tarjeta_numero: '',
  tarjeta_exp_mes: '',
  tarjeta_exp_anio: '',
  tarjeta_cvv: '',
})

const numeroFormateado = computed({
  get: () => {
    const soloDigitos = (pagoForm.tarjeta_numero || '').replace(/\D/g, '').slice(0, 19)
    return soloDigitos.replace(/(.{4})/g, '$1 ').trim()
  },
  set: (val) => {
    pagoForm.tarjeta_numero = String(val || '').replace(/\D/g, '').slice(0, 19)
  },
})

const marcaTarjeta = computed(() => {
  const d = (pagoForm.tarjeta_numero || '').replace(/\D/g, '')
  if (/^4/.test(d)) return 'Visa'
  if (/^(5[1-5]|222[1-9]|22[3-9]\d|2[3-6]\d{2}|27[01]\d|2720)/.test(d)) return 'Mastercard'
  if (/^3[47]/.test(d)) return 'American Express'
  if (/^6(?:011|5)/.test(d)) return 'Discover'
  return ''
})

const ultimos4Preview = computed(() => {
  const d = (pagoForm.tarjeta_numero || '').replace(/\D/g, '')
  return d.length >= 4 ? d.slice(-4) : '••••'
})

function aprobar() {
  mostrarModal.value = true
}

function confirmarAprobar() {
  form.post(route('panel.cotizaciones.aprobar', { id: props.cotizacion.id }), {
    onSuccess: () => { mostrarModal.value = false; form.reset() },
  })
}

function rechazar() {
  if (confirm('¿Rechazar esta cotización?')) {
    form.post(route('panel.cotizaciones.rechazar', { id: props.cotizacion.id }))
  }
}

function aprobarCliente() {
  if (confirm('¿Deseas aprobar esta cotización?')) {
    router.post(route('panel.cotizaciones.aprobar.cliente', { id: props.cotizacion.id }))
  }
}

function procesarPagoTarjeta() {
  const ruta = props.cotizacion?.es_cliente
    ? route('panel.cotizaciones.pagar.cliente', { id: props.cotizacion.id })
    : route('panel.cotizaciones.pagar', { id: props.cotizacion.id })

  pagoForm.post(ruta, {
    preserveScroll: true,
    onSuccess: () => pagoForm.reset(),
    onFinish: () => {
      pagoForm.tarjeta_numero = ''
      pagoForm.tarjeta_cvv = ''
    },
  })
}

function formato(val) { return val || '—' }
function moneda(val) { return Number(val || 0).toFixed(2) }
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ formato(cotizacion?.folio) }}</h1>
          <p class="text-sm text-gray-500 mt-1">Detalle de cotización</p>
        </div>
        <NeumorphicButton type="button" @click="router.visit(cotizacion?.es_cliente ? route('panel.cliente.cotizaciones') : route('panel.cotizaciones.index'))">Volver</NeumorphicButton>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="neumorphic-card p-6 lg:col-span-2 space-y-5">
          <div class="grid grid-cols-2 gap-4">
            <div><p class="text-xs text-gray-500 uppercase">Cliente</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.cliente) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Fecha</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.fecha) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Tipo de Servicio</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.tipo) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Estatus</p><Badge :variant="cotizacion?.estatus === 'aprobado' ? 'success' : cotizacion?.estatus === 'rechazado' ? 'danger' : 'warning'">{{ $etiqueta(cotizacion?.estatus || 'pendiente') }}</Badge></div>
            <div class="col-span-2"><p class="text-xs text-gray-500 uppercase">Origen</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.origen) }}</p></div>
            <div class="col-span-2"><p class="text-xs text-gray-500 uppercase">Destino</p><p class="text-gray-800 font-medium">{{ formato(cotizacion?.destino) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Distancia</p><p class="text-gray-800 font-medium">{{ cotizacion?.distancia ? cotizacion.distancia + ' km' : '—' }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Banderazo</p><p class="text-gray-800 font-medium">${{ moneda(cotizacion?.banderazo) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Kilómetros excedentes</p><p class="text-gray-800 font-medium">{{ cotizacion?.km_excedente || 0 }} km × ${{ moneda(cotizacion?.costo_km) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Subtotal</p><p class="text-gray-800 font-medium">${{ moneda(cotizacion?.subtotal) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Descuento</p><p class="text-gray-800 font-medium">{{ cotizacion?.descuento_pct || 0 }}% (${{ moneda(cotizacion?.monto_descuento) }})</p></div>
            <div><p class="text-xs text-gray-500 uppercase">IVA</p><p class="text-gray-800 font-medium">${{ moneda(cotizacion?.iva) }}</p></div>
            <div><p class="text-xs text-gray-500 uppercase">Casetas</p><p class="text-gray-800 font-medium">${{ moneda(cotizacion?.casetas) }} <span class="text-xs">{{ cotizacion?.incluye_peajes ? '(incluidas)' : '(cargo adicional)' }}</span></p></div>
            <div><p class="text-xs text-gray-500 uppercase">Total Estimado</p><p class="text-xl font-bold text-[var(--color-primary)]">${{ moneda(cotizacion?.total_estimado) }} MXN</p></div>
          </div>

          <!-- Botones de aprobación (solo si cuenta con convenio con aseguradora) -->
          <div v-if="cotizacion?.estatus === 'pendiente' && cotizacion?.puede_aprobar_cliente && !cotizacion?.requiere_pago_tarjeta" class="flex gap-3 pt-3 border-t border-gray-200">
            <NeumorphicButton type="button" @click="aprobarCliente" class="!bg-green-600 !text-white">Aprobar Cotización</NeumorphicButton>
          </div>

          <p v-if="cotizacion?.estatus === 'pendiente' && cotizacion?.requiere_pago_tarjeta && cotizacion?.estatus_pago !== 'pagado'" class="text-sm text-amber-700 border-t border-gray-200 pt-3">
            Cliente particular sin convenio con aseguradora: requiere completar el cobro con tarjeta para aprobar la cotización y asignar el servicio.
          </p>
          <p v-else-if="cotizacion?.estatus === 'pendiente' && !cotizacion?.puede_aprobar_cliente && !cotizacion?.cliente_aprobada_at" class="text-sm text-amber-700 border-t border-gray-200 pt-3">
            Pendiente de aprobación por el cliente.
          </p>
          <p v-if="cotizacion?.cliente_aprobada_at && cotizacion?.estatus === 'pendiente' && cotizacion?.es_cliente" class="text-sm text-emerald-700 border-t border-gray-200 pt-3">
            Tu cotización fue aprobada{{ cotizacion?.estatus_pago === 'pagado' ? ' y pagada' : '' }}. Está en espera de la asignación de operador y unidad.
          </p>

          <template v-if="cotizacion?.estatus === 'pendiente' && cotizacion?.cliente_aprobada_at && !cotizacion?.es_cliente && (!cotizacion?.requiere_pago_tarjeta || cotizacion?.estatus_pago === 'pagado')">
            <p class="text-sm text-emerald-700 border-t border-gray-200 pt-3">
              {{ cotizacion?.requiere_pago_tarjeta ? 'Pago con tarjeta confirmado.' : 'Cliente aprobado.' }} Ya puedes realizar la aprobación interna y asignar operador y unidad.
            </p>
            <div class="flex gap-3 pt-3">
              <NeumorphicButton type="button" @click="aprobar" class="!bg-green-600 !text-white">Aprobar y Crear Servicio</NeumorphicButton>
              <NeumorphicButton type="button" variant="danger" @click="rechazar">Rechazar</NeumorphicButton>
            </div>
          </template>

          <div v-if="cotizacion?.servicio_id" class="pt-3 border-t border-gray-200">
            <p class="text-sm text-green-700">Servicio #{{ cotizacion.servicio_id }} creado</p>
          </div>
        </div>

        <!-- Columna lateral: Convenio y Cobro con Tarjeta (siempre disponible en cotizaciones pendientes sin pagar) -->
        <div class="space-y-6">
          <!-- Información de convenio si aplica -->
          <div v-if="cotizacion?.tiene_convenio && cotizacion?.estatus_pago !== 'pagado'" class="neumorphic-card p-6 space-y-3">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-bold text-gray-800">Forma de Cobro</h2>
              <Badge variant="success">Convenio</Badge>
            </div>
            <p class="text-sm text-gray-600">
              Esta cotización cuenta con convenio de aseguradora. Puedes aprobarla con tu convenio o, si lo prefieres, pagarla directamente con tarjeta abajo.
            </p>
            <div v-if="cotizacion?.aseguradora || cotizacion?.convenio" class="rounded-2xl bg-white/70 p-3.5 text-sm space-y-1">
              <p v-if="cotizacion?.aseguradora"><span class="text-gray-500">Aseguradora:</span> <strong>{{ cotizacion.aseguradora }}</strong></p>
              <p v-if="cotizacion?.convenio"><span class="text-gray-500">Convenio:</span> <strong>{{ cotizacion.convenio }}</strong></p>
            </div>
          </div>

          <!-- Comprobante si el pago con tarjeta ya fue realizado -->
          <div v-if="cotizacion?.estatus_pago === 'pagado' && cotizacion?.pago" class="neumorphic-card p-6 space-y-4">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-bold text-gray-800">Comprobante de Pago</h2>
              <Badge variant="success">Pagado</Badge>
            </div>
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 space-y-2 text-sm">
              <div class="flex justify-between"><span class="text-emerald-700">Referencia:</span><strong class="text-emerald-900">{{ cotizacion.pago.referencia }}</strong></div>
              <div class="flex justify-between"><span class="text-emerald-700">Tarjeta:</span><strong class="text-emerald-900">{{ cotizacion.pago.tarjeta_marca }} •••• {{ cotizacion.pago.tarjeta_ultimos4 }}</strong></div>
              <div class="flex justify-between"><span class="text-emerald-700">Titular:</span><span class="font-medium text-emerald-900">{{ cotizacion.pago.tarjeta_titular }}</span></div>
              <div class="flex justify-between"><span class="text-emerald-700">Monto:</span><strong class="text-emerald-900">${{ moneda(cotizacion.pago.monto) }} {{ cotizacion.pago.moneda }}</strong></div>
              <div class="flex justify-between"><span class="text-emerald-700">Fecha:</span><span class="text-emerald-900">{{ cotizacion.pago.pagado_en }}</span></div>
            </div>
          </div>

          <!-- Formulario de cobro con tarjeta (siempre visible mientras la cotización esté pendiente y sin pagar) -->
          <div v-if="cotizacion?.puede_pagar_tarjeta" class="neumorphic-card p-6 space-y-4">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-bold text-gray-800">Pago con Tarjeta</h2>
              <Badge :variant="cotizacion?.requiere_pago_tarjeta ? 'warning' : 'info'">
                {{ cotizacion?.requiere_pago_tarjeta ? 'Requerido' : 'Opcional' }}
              </Badge>
            </div>

            <!-- Tarjeta visual previa -->
            <div class="rounded-2xl p-4 text-white shadow-lg space-y-3" style="background: linear-gradient(135deg, var(--color-primary, #4F46E5), var(--color-secondary, #7C3AED));">
              <div class="flex items-center justify-between text-xs uppercase tracking-wider opacity-90">
                <span>Cobro Seguro</span>
                <span class="font-bold">{{ marcaTarjeta || 'Crédito / Débito' }}</span>
              </div>
              <p class="font-mono text-base tracking-widest">
                •••• •••• •••• {{ ultimos4Preview }}
              </p>
              <div class="flex items-center justify-between text-xs opacity-90">
                <span class="truncate max-w-[65%] uppercase">{{ pagoForm.tarjeta_titular || 'NOMBRE DEL TITULAR' }}</span>
                <span>{{ pagoForm.tarjeta_exp_mes ? String(pagoForm.tarjeta_exp_mes).padStart(2, '0') : 'MM' }}/{{ pagoForm.tarjeta_exp_anio ? String(pagoForm.tarjeta_exp_anio).slice(-2) : 'AA' }}</span>
              </div>
            </div>

            <form @submit.prevent="procesarPagoTarjeta" class="space-y-3.5">
              <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Titular de la tarjeta</label>
                <input
                  v-model="pagoForm.tarjeta_titular"
                  type="text"
                  required
                  autocomplete="cc-name"
                  placeholder="Como aparece en la tarjeta"
                  class="w-full bg-[#E8EDF2] text-gray-800 rounded-2xl px-4 py-2.5 text-sm shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />
                <p v-if="pagoForm.errors.tarjeta_titular" class="text-xs text-red-500 mt-1">{{ pagoForm.errors.tarjeta_titular }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Número de tarjeta</label>
                <input
                  v-model="numeroFormateado"
                  type="text"
                  inputmode="numeric"
                  required
                  autocomplete="cc-number"
                  maxlength="23"
                  placeholder="0000 0000 0000 0000"
                  class="w-full bg-[#E8EDF2] text-gray-800 rounded-2xl px-4 py-2.5 text-sm font-mono tracking-wider shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />
                <p v-if="pagoForm.errors.tarjeta_numero" class="text-xs text-red-500 mt-1">{{ pagoForm.errors.tarjeta_numero }}</p>
              </div>

              <div class="grid grid-cols-3 gap-2.5">
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Mes</label>
                  <select
                    v-model="pagoForm.tarjeta_exp_mes"
                    required
                    autocomplete="cc-exp-month"
                    class="w-full bg-[#E8EDF2] text-gray-800 rounded-2xl px-3 py-2.5 text-sm shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300"
                  >
                    <option value="">MM</option>
                    <option v-for="m in meses" :key="m.value" :value="m.value">{{ m.label }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Año</label>
                  <select
                    v-model="pagoForm.tarjeta_exp_anio"
                    required
                    autocomplete="cc-exp-year"
                    class="w-full bg-[#E8EDF2] text-gray-800 rounded-2xl px-3 py-2.5 text-sm shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300"
                  >
                    <option value="">AAAA</option>
                    <option v-for="a in anios" :key="a" :value="a">{{ a }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">CVV</label>
                  <input
                    v-model="pagoForm.tarjeta_cvv"
                    type="password"
                    inputmode="numeric"
                    required
                    autocomplete="cc-csc"
                    maxlength="4"
                    placeholder="•••"
                    class="w-full bg-[#E8EDF2] text-gray-800 rounded-2xl px-3 py-2.5 text-sm font-mono shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300"
                  />
                </div>
              </div>
              <p v-if="pagoForm.errors.tarjeta_exp_mes || pagoForm.errors.tarjeta_exp_anio" class="text-xs text-red-500">
                {{ pagoForm.errors.tarjeta_exp_mes || pagoForm.errors.tarjeta_exp_anio }}
              </p>
              <p v-if="pagoForm.errors.tarjeta_cvv" class="text-xs text-red-500">{{ pagoForm.errors.tarjeta_cvv }}</p>
              <p v-if="pagoForm.errors.tarjeta" class="text-xs text-red-500">{{ pagoForm.errors.tarjeta }}</p>

              <NeumorphicButton type="submit" :loading="pagoForm.processing" class="w-full">
                Pagar ${{ moneda(cotizacion?.total_estimado) }} MXN y Aprobar
              </NeumorphicButton>

              <p class="text-[11px] text-gray-500 leading-relaxed text-center">
                Protección de datos PCI: nunca almacenamos el número completo de tu tarjeta ni el CVV; solo guardamos los últimos 4 dígitos y la referencia de pago.
              </p>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal de asignación -->
      <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="mostrarModal = false">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md mx-4 shadow-2xl space-y-4">
          <h2 class="text-lg font-semibold text-gray-800">Asignar Operador y Unidad</h2>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Operador</label>
            <select v-model="form.operador_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
              <option value="">Seleccionar operador...</option>
              <option v-for="o in operadores" :key="o.id" :value="o.id">{{ o.nombre }}</option>
            </select>
            <p v-if="form.errors.operador_id" class="text-sm text-red-500 mt-1">{{ form.errors.operador_id }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Unidad</label>
            <select v-model="form.unidad_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
              <option value="">Seleccionar unidad...</option>
              <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.placas }} - {{ u.numero_economico }}</option>
            </select>
            <p v-if="form.errors.unidad_id" class="text-sm text-red-500 mt-1">{{ form.errors.unidad_id }}</p>
          </div>
          <p v-if="form.errors.cotizacion" class="text-sm text-red-500">{{ form.errors.cotizacion }}</p>
          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="button" @click="confirmarAprobar()" :loading="form.processing" :disabled="!form.operador_id || !form.unidad_id">Confirmar y Crear Servicio</NeumorphicButton>
            <NeumorphicButton type="button" variant="secondary" @click="mostrarModal = false">Cancelar</NeumorphicButton>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: var(--color-surface); border-radius: 24px; box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light); }
</style>

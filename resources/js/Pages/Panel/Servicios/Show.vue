<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

    const props = defineProps({
      servicio: Object,
    })

    function formatKm(val) {
      if (val === null || val === undefined || val === '') return null
      return Number(val).toLocaleString() + ' km'
    }
    </script>

    <template>
      <!-- Detalle de servicio con bitácora -->
      <AppLayout>
        <div class="space-y-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-800">{{ servicio?.folio || '—' }}</h1>
              <p class="text-sm text-gray-500 mt-1">Detalle del servicio</p>
            </div>
            <NeumorphicButton @click="router.visit(route('panel.servicios.index'))">Volver</NeumorphicButton>
          </div>

          <!-- Tarjeta principal con datos del servicio -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="neumorphic-card p-6 lg:col-span-2 space-y-5">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider">Cliente</p>
                  <p class="text-gray-800 font-medium">{{ servicio?.cliente || '—' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider">Fecha</p>
                  <p class="text-gray-800 font-medium">{{ servicio?.fecha || '—' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider">Operador</p>
                  <p class="text-gray-800 font-medium">{{ servicio?.operador || '—' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider">Unidad</p>
                  <p class="text-gray-800 font-medium">{{ servicio?.unidad || '—' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider">Tipo de Servicio</p>
                  <p class="text-gray-800 font-medium">{{ servicio?.tipo || '—' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider">Estatus</p>
                  <Badge :variant="servicio?.estatus || 'neutral'">{{ servicio?.estatus || '—' }}</Badge>
                </div>
                <div class="col-span-2">
                  <p class="text-xs text-gray-500 uppercase tracking-wider">Origen</p>
                  <p class="text-gray-800 font-medium">{{ servicio?.origen || '—' }}</p>
                </div>
                <div class="col-span-2">
                  <p class="text-xs text-gray-500 uppercase tracking-wider">Destino</p>
                  <p class="text-gray-800 font-medium">{{ servicio?.destino || '—' }}</p>
                </div>
                <div class="col-span-2">
                  <p class="text-xs text-gray-500 uppercase tracking-wider">Observaciones</p>
                  <p class="text-gray-600">{{ servicio?.observaciones || '—' }}</p>
                </div>
              </div>
            </div>

            <!-- Bitácora de tiempos -->
            <div class="space-y-4">
              <div class="neumorphic-card p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Bitácora de Tiempos</h3>
                <div class="space-y-2 text-sm">
                  <p class="text-gray-500">Salida: <span class="text-gray-800">{{ servicio?.bitacora?.salida || '—' }}</span></p>
                  <p class="text-gray-500">Llegada a cliente: <span class="text-gray-800">{{ servicio?.bitacora?.llegada || '—' }}</span></p>
                  <p class="text-gray-500">Término servicio: <span class="text-gray-800">{{ servicio?.bitacora?.termino || '—' }}</span></p>
                  <p class="text-gray-500">Regreso base: <span class="text-gray-800">{{ servicio?.bitacora?.regreso || '—' }}</span></p>
                </div>
              </div>

              <!-- Kilometraje -->
              <div class="neumorphic-card p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Kilometraje</h3>
                <div class="space-y-2 text-sm">
                  <p class="text-gray-500">Salida: <span class="text-gray-800">{{ formatKm(servicio?.kms_salida) || '—' }}</span></p>
                  <p class="text-gray-500">Llegada cliente: <span class="text-gray-800">{{ formatKm(servicio?.kms_llegada_cliente) || '—' }}</span></p>
                  <p class="text-gray-500">Término servicio: <span class="text-gray-800">{{ formatKm(servicio?.kms_termino_servicio) || '—' }}</span></p>
                  <p class="text-gray-500">Regreso: <span class="text-gray-800">{{ formatKm(servicio?.kms_regreso_base) || '—' }}</span></p>
                  <p class="text-gray-500">Cobrados: <span class="text-gray-800">{{ formatKm(servicio?.kms_cobrados_reales) || '—' }}</span></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </AppLayout>
    </template>

<style scoped>
.neumorphic-card {
  background: #EEF2F7;
  border-radius: 24px;
  box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff;
}
</style>

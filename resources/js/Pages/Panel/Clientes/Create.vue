<script setup>
import { ref, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'
import { ESTADOS, municipiosPorEstado } from '@/Data/estadosMunicipios'

const page = usePage()
const cliente = page.props.cliente
const aseguradoras = page.props.aseguradoras ?? []
const editMode = !!cliente

const municipios = ref([])

function actualizarMunicipios() {
  municipios.value = municipiosPorEstado(form.estado)
  if (form.estado && !municipios.value.includes(form.municipio_alcaldia)) {
    form.municipio_alcaldia = ''
  }
}

const form = useForm({
  nombre: cliente?.nombre ?? '',
  apellido_paterno: cliente?.apellido_paterno ?? '',
  apellido_materno: cliente?.apellido_materno ?? '',
  tipo_cliente: cliente?.tipo_cliente ?? '',
  sexo: cliente?.sexo ?? '',
  curp: cliente?.curp ?? '',
  fecha_nacimiento: cliente?.fecha_nacimiento ?? '',
  telefono: cliente?.telefono ?? '',
  telefono_local: cliente?.telefono_local ?? '',
  email: cliente?.email ?? '',
  folio_ine: cliente?.folio_ine ?? '',
  nacionalidad: cliente?.nacionalidad ?? '',
  contacto_enlace: cliente?.contacto_enlace ?? '',
  aseguradora_id: cliente?.aseguradora_id ?? '',
  numero_poliza: cliente?.numero_poliza ?? '',
  tipo_cobertura_poliza: cliente?.tipo_cobertura_poliza ?? '',
  calle: cliente?.direccion?.calle ?? '',
  numero_exterior: cliente?.direccion?.numero_exterior ?? '',
  numero_interior: cliente?.direccion?.numero_interior ?? '',
  colonia: cliente?.direccion?.colonia ?? '',
  codigo_postal: cliente?.direccion?.codigo_postal ?? '',
  municipio_alcaldia: cliente?.direccion?.municipio_alcaldia ?? '',
  ciudad: cliente?.direccion?.ciudad ?? '',
  estado: cliente?.direccion?.estado ?? '',
  pais: cliente?.direccion?.pais ?? 'México',
  referencias: cliente?.direccion?.referencias ?? '',
})

if (form.estado) {
  municipios.value = municipiosPorEstado(form.estado)
}

const rules = {
  nombre: ['required', 'min:2', 'max:255'],
  tipo_cliente: ['selectRequired'],
  nacionalidad: ['selectRequired'],
  curp: ['curp'],
  fecha_nacimiento: ['date'],
  telefono: ['phone'],
  telefono_local: ['phone'],
  email: ['email'],
}
const val = useFormValidation(form, rules)

function doSubmit() {
  if (!val.validate()) return
  if (editMode) {
    form.put(route('panel.clientes.update', cliente.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.clientes.store'), {
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <!-- Formulario de registro / edición de cliente -->
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado del formulario -->
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ editMode ? 'Editar Cliente' : 'Nuevo Cliente' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ editMode ? 'Actualiza los datos del cliente' : 'Registra un nuevo cliente' }}</p>
      </div>

      <!-- Formulario de datos del cliente -->
      <div class="neumorphic-card p-6 max-w-4xl">
        <form @submit.prevent="doSubmit" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Nombre</label>
              <NeumorphicInput v-model="form.nombre" placeholder="Nombre(s)" :error="val.getError('nombre')" @input="val.handleInput('nombre')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Apellido Paterno</label>
              <NeumorphicInput v-model="form.apellido_paterno" placeholder="Apellido paterno" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Apellido Materno</label>
              <NeumorphicInput v-model="form.apellido_materno" placeholder="Apellido materno" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Cliente</label>
              <select v-model="form.tipo_cliente" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option value="persona_fisica">Persona Física</option>
                <option value="persona_moral">Persona Moral</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Sexo</label>
              <select v-model="form.sexo" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">CURP</label>
              <NeumorphicInput v-model="form.curp" placeholder="CURP" maxlength="18" :error="val.getError('curp')" @input="val.handleInput('curp')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Fecha de Nacimiento</label>
              <NeumorphicInput v-model="form.fecha_nacimiento" type="date" :error="val.getError('fecha_nacimiento')" @input="val.handleInput('fecha_nacimiento')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Teléfono</label>
              <NeumorphicInput v-model="form.telefono" placeholder="Teléfono" :error="val.getError('telefono')" @input="val.handleInput('telefono')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Teléfono Local</label>
              <NeumorphicInput v-model="form.telefono_local" placeholder="Teléfono local" :error="val.getError('telefono_local')" @input="val.handleInput('telefono_local')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
              <NeumorphicInput v-model="form.email" type="email" placeholder="correo@ejemplo.com" :error="val.getError('email')" @input="val.handleInput('email')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Folio INE</label>
              <NeumorphicInput v-model="form.folio_ine" placeholder="Folio INE" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Nacionalidad</label>
              <select v-model="form.nacionalidad" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option value="Mexicana">Mexicana</option>
                <option value="Extranjera">Extranjera</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Contacto Enlace</label>
              <NeumorphicInput v-model="form.contacto_enlace" placeholder="Contacto de enlace" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Aseguradora</label>
              <select v-model="form.aseguradora_id" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Sin aseguradora</option>
                <option v-for="a in aseguradoras" :key="a.id" :value="a.id">{{ a.nombre }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Número de Póliza</label>
              <NeumorphicInput v-model="form.numero_poliza" placeholder="Número de póliza" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Tipo de Cobertura</label>
              <select v-model="form.tipo_cobertura_poliza" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Seleccionar...</option>
                <option value="Responsabilidad Civil">Responsabilidad Civil</option>
                <option value="Cobertura Amplia">Cobertura Amplia</option>
                <option value="Cobertura Limitada">Cobertura Limitada</option>
                <option value="Daños a Terceros">Daños a Terceros</option>
              </select>
            </div>
          </div>

          <!-- Sección de dirección -->
          <div class="border-t border-gray-200 pt-4">
            <p class="text-sm font-medium text-gray-600 mb-3">Dirección</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Calle</label>
                <NeumorphicInput v-model="form.calle" placeholder="Calle" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Número Exterior</label>
                <NeumorphicInput v-model="form.numero_exterior" placeholder="No. exterior" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Número Interior</label>
                <NeumorphicInput v-model="form.numero_interior" placeholder="No. interior (opcional)" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Colonia</label>
                <NeumorphicInput v-model="form.colonia" placeholder="Colonia" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Código Postal</label>
                <NeumorphicInput v-model="form.codigo_postal" placeholder="Código postal" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Estado</label>
                <select v-model="form.estado" @change="actualizarMunicipios()" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300">
                  <option value="">Seleccionar estado...</option>
                  <option v-for="e in ESTADOS" :key="e" :value="e">{{ e }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Municipio / Alcaldía</label>
                <select v-model="form.municipio_alcaldia" :disabled="!form.estado" class="w-full bg-[#E8EDF2] text-gray-700 rounded-2xl p-3 shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:opacity-50">
                  <option value="">Seleccionar municipio...</option>
                  <option v-for="m in municipios" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Localidad</label>
                <NeumorphicInput v-model="form.ciudad" placeholder="Localidad" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">País</label>
                <NeumorphicInput v-model="form.pais" placeholder="País" />
              </div>
              <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-600 mb-1">Referencias</label>
                <NeumorphicInput v-model="form.referencias" placeholder="Referencias (opcional)" />
              </div>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ editMode ? 'Actualizar Cliente' : 'Guardar Cliente' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.clientes.index'))">Cancelar</NeumorphicButton>
          </div>
        </form>
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

<script setup>
import { ref, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import { useFormValidation } from '@/Composables/useFormValidation'
import { ESTADOS, municipiosPorEstado } from '@/Data/estadosMunicipios'

const page = usePage()
const empleado = page.props.empleado
const oficinas = page.props.oficinas ?? []
const editMode = !!empleado

const municipios = ref([])

function actualizarMunicipios() {
  municipios.value = municipiosPorEstado(form.direccion.estado)
  const estado = form.direccion.estado
  if (estado && !municipios.value.includes(form.direccion.municipio_alcaldia)) {
    form.direccion.municipio_alcaldia = ''
  }
}

const form = useForm({
  nombre: empleado?.nombre ?? '',
  apellido_paterno: empleado?.apellido_paterno ?? '',
  apellido_materno: empleado?.apellido_materno ?? '',
  sexo: empleado?.sexo ?? '',
  curp: empleado?.curp ?? '',
  fecha_nacimiento: empleado?.fecha_nacimiento ?? '',
  telefono: empleado?.telefono ?? '',
  telefono_local: empleado?.telefono_local ?? '',
  correo: empleado?.correo ?? '',
  folio_ine: empleado?.folio_ine ?? '',
  nacionalidad: empleado?.nacionalidad ?? 'Mexicana',
  puesto: empleado?.puesto ?? '',
  sueldo_diario: empleado?.sueldo_diario ?? '',
  oficina_id: empleado?.oficina_id ?? '',
  direccion: {
    calle: empleado?.direccion?.calle ?? '',
    numero_exterior: empleado?.direccion?.numero_exterior ?? '',
    numero_interior: empleado?.direccion?.numero_interior ?? '',
    colonia: empleado?.direccion?.colonia ?? '',
    codigo_postal: empleado?.direccion?.codigo_postal ?? '',
    municipio_alcaldia: empleado?.direccion?.municipio_alcaldia ?? '',
    ciudad: empleado?.direccion?.ciudad ?? '',
    estado: empleado?.direccion?.estado ?? '',
    pais: empleado?.direccion?.pais ?? 'México',
    referencias: empleado?.direccion?.referencias ?? '',
  },
})

if (form.direccion.estado) {
  municipios.value = municipiosPorEstado(form.direccion.estado)
}

const rules = {
  nombre: ['required', 'min:2', 'max:255'],
  apellido_paterno: ['required', 'min:2', 'max:255'],
  curp: ['curp'],
  fecha_nacimiento: ['date'],
  telefono: ['phone'],
  telefono_local: ['phone'],
  correo: ['email'],
  sueldo_diario: ['numeric', 'min_value:0'],
}
const val = useFormValidation(form, rules)

function submit() {
  if (editMode) {
    form.put(route('panel.empleados.update', empleado.id), {
      onSuccess: () => form.reset(),
    })
  } else {
    form.post(route('panel.empleados.store'), {
      onSuccess: () => form.reset(),
    })
  }
}

const onSubmit = val.handleSubmit(submit)
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-[var(--color-text)]">{{ editMode ? 'Editar Empleado' : 'Nuevo Empleado' }}</h1>
        <p class="text-sm text-[var(--color-text-muted)] mt-1">{{ editMode ? 'Actualiza los datos del empleado' : 'Registra un nuevo empleado' }}</p>
      </div>

      <div class="neumorphic-card p-6 max-w-4xl">
        <form @submit.prevent="onSubmit" class="space-y-6">
          <h2 class="text-lg font-semibold text-[var(--color-text)] border-b border-[var(--neumorphic-dark)]/20 pb-2">Datos personales</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <NeumorphicInput v-model="form.nombre" label="Nombre(s)*" placeholder="Ej: Juan Carlos" required :error="val.getError('nombre')" @input="val.handleInput('nombre')" />
            <NeumorphicInput v-model="form.apellido_paterno" label="Apellido paterno*" placeholder="Ej: García" required :error="val.getError('apellido_paterno')" @input="val.handleInput('apellido_paterno')" />
            <NeumorphicInput v-model="form.apellido_materno" label="Apellido materno" placeholder="Ej: Hernández" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
              <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Sexo</label>
              <select v-model="form.sexo" class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl p-3 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
                <option value="">Seleccionar sexo...</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
              </select>
            </div>
            <NeumorphicInput v-model="form.curp" label="CURP" placeholder="Ej: GARC850101MDFRRN01" maxlength="18" :error="val.getError('curp')" @input="val.handleInput('curp')" />
            <NeumorphicInput v-model="form.fecha_nacimiento" label="Fecha de nacimiento" type="date" :error="val.getError('fecha_nacimiento')" @input="val.handleInput('fecha_nacimiento')" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <NeumorphicInput v-model="form.telefono" label="Teléfono" placeholder="Ej: 5512345678" :error="val.getError('telefono')" @input="val.handleInput('telefono')" />
            <NeumorphicInput v-model="form.telefono_local" label="Teléfono local" placeholder="Ej: 5556789012" :error="val.getError('telefono_local')" @input="val.handleInput('telefono_local')" />
            <NeumorphicInput v-model="form.correo" label="Correo electrónico" type="email" placeholder="correo@ejemplo.com" :error="val.getError('correo')" @input="val.handleInput('correo')" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <NeumorphicInput v-model="form.folio_ine" label="Folio INE" placeholder="Ej: 1234567890123" />
            <div>
              <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Nacionalidad</label>
              <select v-model="form.nacionalidad" class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl p-3 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
                <option value="Mexicana">Mexicana</option>
                <option value="Extranjera">Extranjera</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Puesto</label>
              <select v-model="form.puesto" class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl p-3 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
                <option value="">Seleccionar puesto...</option>
                <option value="admin">Admin</option>
                <option value="cotizador">Cotizador</option>
                <option value="operador">Operador</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <NeumorphicInput v-model="form.sueldo_diario" label="Sueldo diario ($)" type="number" step="0.01" placeholder="0.00" :error="val.getError('sueldo_diario')" @input="val.handleInput('sueldo_diario')" />
            <div>
              <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Oficina</label>
              <select v-model="form.oficina_id" class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl p-3 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
                <option value="">Seleccionar oficina...</option>
                <option v-for="o in oficinas" :key="o.id" :value="o.id">{{ o.nombre }}</option>
              </select>
            </div>
          </div>

          <h2 class="text-lg font-semibold text-[var(--color-text)] border-b border-[var(--neumorphic-dark)]/20 pb-2">Dirección</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <NeumorphicInput v-model="form.direccion.calle" label="Calle" placeholder="Ej: Av. Reforma" />
            <NeumorphicInput v-model="form.direccion.numero_exterior" label="Núm. exterior" placeholder="Ej: 123" />
            <NeumorphicInput v-model="form.direccion.numero_interior" label="Núm. interior" placeholder="Ej: 4B" />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <NeumorphicInput v-model="form.direccion.colonia" label="Colonia" placeholder="Ej: Centro" />
            <NeumorphicInput v-model="form.direccion.codigo_postal" label="Código postal" placeholder="Ej: 06600" />
            <div>
              <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Estado</label>
              <select v-model="form.direccion.estado" @change="actualizarMunicipios()" class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl p-3 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
                <option value="">Seleccionar estado...</option>
                <option v-for="e in ESTADOS" :key="e" :value="e">{{ e }}</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
              <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Municipio / Alcaldía</label>
              <select v-model="form.direccion.municipio_alcaldia" :disabled="!form.direccion.estado" class="w-full bg-[var(--color-bg)] text-[var(--color-text)] rounded-2xl p-3 shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] disabled:opacity-50">
                <option value="">Seleccionar municipio...</option>
                <option v-for="m in municipios" :key="m" :value="m">{{ m }}</option>
              </select>
            </div>
            <NeumorphicInput v-model="form.direccion.ciudad" label="Localidad" placeholder="Ej: Centro Histórico" />
            <NeumorphicInput v-model="form.direccion.pais" label="País" placeholder="México" />
          </div>
          <div class="grid grid-cols-1 gap-5">
            <NeumorphicInput v-model="form.direccion.referencias" label="Referencias" placeholder="Ej: Entre calles X y Y" />
          </div>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="submit" :loading="form.processing">{{ editMode ? 'Actualizar Empleado' : 'Guardar Empleado' }}</NeumorphicButton>
            <NeumorphicButton variant="secondary" @click="router.visit(route('panel.empleados.index'))">Cancelar</NeumorphicButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card {
  background: var(--color-surface);
  border-radius: 24px;
  box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light);
}
</style>

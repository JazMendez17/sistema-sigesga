<script setup>
import { ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'

const page = usePage()
const usuario = page.props.usuario
const empleado = page.props.empleado || null
const cliente = page.props.cliente || null

const editingTelefono = ref(false)
const editingPassword = ref(false)
const uploading = ref(false)
const previewUrl = ref(null)
const previewFile = ref(null)
const deleting = ref(false)

const telefonoForm = useForm({
  telefono: usuario.telefono || '',
})

const passwordForm = useForm({
  actual: '',
  nueva: '',
  confirmar: '',
})

function actualizarTelefono() {
  telefonoForm.put(route('panel.mi-perfil.telefono'), {
    preserveScroll: true,
    onSuccess: () => {
      editingTelefono.value = false
    },
  })
}

function cambiarPassword() {
  if (passwordForm.nueva !== passwordForm.confirmar) {
    passwordForm.setError('confirmar', 'Las contraseñas no coinciden')
    return
  }
  passwordForm.put(route('panel.mi-perfil.password'), {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset()
      editingPassword.value = false
    },
  })
}

function previewFoto(e) {
  const file = e.target.files?.[0]
  if (!file) return
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    alert('Solo se permiten imágenes JPG, PNG o WEBP')
    e.target.value = ''
    return
  }
  if (file.size > 2 * 1024 * 1024) {
    alert('La imagen no debe superar los 2 MB')
    e.target.value = ''
    return
  }
  const reader = new FileReader()
  reader.onload = (ev) => {
    previewUrl.value = ev.target.result
  }
  reader.readAsDataURL(file)
}

function confirmUpload() {
  if (!previewFile.value) return
  uploading.value = true
  router.post(route('panel.mi-perfil.foto'), {
    foto: previewFile.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      previewUrl.value = null
      previewFile.value = null
    },
    onFinish: () => {
      uploading.value = false
    },
  })
}

function cancelPreview() {
  previewUrl.value = null
  previewFile.value = null
}

function deleteFoto() {
  if (!confirm('¿Eliminar foto de perfil?')) return
  deleting.value = true
  router.delete(route('panel.mi-perfil.foto.destroy'), {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false
    },
  })
}

const rolLabel = {
  admin: 'Administrador',
  cotizador: 'Cotizador',
  operador: 'Operador',
  cliente: 'Cliente',
}

const perfilData = empleado || cliente || null
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold" style="color: var(--color-text)">Mi Perfil</h1>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] lg:col-span-1 space-y-5">
          <div class="flex flex-col items-center">
            <div class="relative">
              <div v-if="previewUrl" class="w-28 h-28 rounded-full overflow-hidden shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] ring-2 ring-[var(--color-primary)]">
                <img :src="previewUrl" class="w-full h-full object-cover" alt="Preview" />
              </div>
              <div v-else-if="usuario.foto" class="w-28 h-28 rounded-full overflow-hidden shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]">
                <img :src="'/storage/' + usuario.foto" class="w-full h-full object-cover" alt="Foto" />
              </div>
              <div v-else class="w-28 h-28 rounded-full flex items-center justify-center text-4xl font-bold text-white shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]" :style="{ backgroundColor: 'var(--color-primary)' }">
                {{ usuario.name.charAt(0) }}
              </div>
              <div v-if="previewUrl" class="absolute -bottom-2 right-6 flex gap-1">
                <button @click="confirmUpload" :disabled="uploading" class="w-9 h-9 rounded-xl bg-green-500 text-white flex items-center justify-center cursor-pointer shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)] hover:scale-105 transition-transform" title="Confirmar">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </button>
                <button @click="cancelPreview" class="w-9 h-9 rounded-xl bg-red-500 text-white flex items-center justify-center cursor-pointer shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)] hover:scale-105 transition-transform" title="Cancelar">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <label v-else class="absolute -bottom-2 -right-2 w-9 h-9 rounded-xl bg-[var(--color-primary)] text-white flex items-center justify-center cursor-pointer shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)] hover:scale-105 transition-transform">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="previewFoto($event); previewFile = $event.target.files?.[0] || null" />
              </label>
            </div>
            <h2 class="mt-5 text-xl font-bold" style="color: var(--color-text)">{{ usuario.name }}</h2>
            <p class="text-sm opacity-60" style="color: var(--color-text)">{{ usuario.email }}</p>
            <div v-if="uploading" class="mt-2 flex items-center gap-2 text-sm" style="color: var(--color-primary)">
              <span class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
              Subiendo...
            </div>
            <button v-if="usuario.foto && !previewUrl" @click="deleteFoto" :disabled="deleting" class="mt-2 text-xs text-red-500 hover:text-red-600 transition-colors flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              <span v-if="deleting">Eliminando...</span>
              <span v-else>Eliminar foto</span>
            </button>
          </div>

          <div class="space-y-3 rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
            <div class="flex justify-between">
              <span class="text-sm opacity-60" style="color: var(--color-text)">Rol</span>
              <span class="text-sm font-semibold px-3 py-0.5 rounded-xl" :style="{ backgroundColor: 'var(--color-primary)', color: '#ffffff' }">{{ rolLabel[usuario.rol] || usuario.rol }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm opacity-60" style="color: var(--color-text)">Empresa</span>
              <span class="text-sm font-medium text-right max-w-[180px]" style="color: var(--color-text)">{{ usuario.empresa }}</span>
            </div>
            <div v-if="empleado?.puesto" class="flex justify-between">
              <span class="text-sm opacity-60" style="color: var(--color-text)">Puesto</span>
              <span class="text-sm font-medium" style="color: var(--color-text)">{{ empleado.puesto }}</span>
            </div>
          </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
          <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-5">
            <h3 class="text-lg font-semibold" style="color: var(--color-text)">Datos del Perfil</h3>

            <div class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Correo Electrónico</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ usuario.email }}</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-lg bg-gray-200/50 text-gray-500">No editable</span>
              </div>
            </div>

            <div class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
              <div v-if="!editingTelefono" class="flex items-center justify-between">
                <div>
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Teléfono</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ usuario.telefono || 'Sin registrar' }}</p>
                </div>
                <button @click="editingTelefono = true" class="text-sm font-medium px-4 py-1.5 rounded-xl text-white transition-all" :style="{ backgroundColor: 'var(--color-primary)' }">
                  Actualizar
                </button>
              </div>
              <div v-else>
                <p class="text-xs opacity-60 uppercase tracking-wider mb-2" style="color: var(--color-text)">Teléfono</p>
                <div class="flex items-center gap-2">
                  <input v-model="telefonoForm.telefono" type="text" class="flex-1 px-4 py-2 rounded-xl text-sm shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)] focus:outline-none" style="background-color: var(--color-bg); color: var(--color-text)" placeholder="Nuevo teléfono" />
                  <button @click="actualizarTelefono" :disabled="telefonoForm.processing" class="text-sm font-medium px-4 py-2 rounded-xl text-white transition-all disabled:opacity-50" :style="{ backgroundColor: 'var(--color-primary)' }">
                    <span v-if="telefonoForm.processing" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin mr-1 align-middle"></span>
                    Guardar
                  </button>
                  <button @click="editingTelefono = false; telefonoForm.telefono = usuario.telefono || ''" class="text-sm px-4 py-2 rounded-xl shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)]" style="color: var(--color-text)">
                    Cancelar
                  </button>
                </div>
                <p v-if="telefonoForm.errors.telefono" class="text-sm text-red-500 mt-1">{{ telefonoForm.errors.telefono }}</p>
              </div>
            </div>
          </div>

          <div v-if="perfilData" class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-4">
            <h3 class="text-lg font-semibold" style="color: var(--color-text)">Datos Registrados</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Nombre Completo</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ perfilData.nombre_completo || '---' }}</p>
              </div>
              <div class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Sexo</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ perfilData.sexo === 'M' ? 'Masculino' : perfilData.sexo === 'F' ? 'Femenino' : '---' }}</p>
              </div>
              <div class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">CURP</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ perfilData.curp || '---' }}</p>
              </div>
              <div class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Fecha de Nacimiento</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ perfilData.fecha_nacimiento || '---' }}</p>
              </div>
              <div class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Nacionalidad</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ perfilData.nacionalidad || '---' }}</p>
              </div>
              <div class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Folio INE</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ perfilData.folio_ine || '---' }}</p>
              </div>
              <div v-if="cliente" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Tipo de Cliente</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ cliente.tipo_cliente }}</p>
              </div>
              <div v-if="cliente?.numero_poliza" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Número de Póliza</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ cliente.numero_poliza }}</p>
              </div>
              <div v-if="cliente?.tipo_cobertura_poliza" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Cobertura</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ cliente.tipo_cobertura_poliza }}</p>
              </div>
              <div v-if="cliente?.aseguradora" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Aseguradora</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ cliente.aseguradora }}</p>
              </div>
              <div v-if="perfilData.direccion" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)] md:col-span-2" style="background-color: var(--color-bg)">
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Dirección</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ perfilData.direccion }}</p>
              </div>
              <template v-if="empleado?.calle || empleado?.colonia">
                <div v-if="empleado?.calle" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Calle</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.calle || '---' }}</p>
                </div>
                <div v-if="empleado?.numero_exterior" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Número Exterior</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.numero_exterior || '---' }}</p>
                </div>
                <div v-if="empleado?.numero_interior" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Número Interior</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.numero_interior }}</p>
                </div>
                <div v-if="empleado?.colonia" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Colonia</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.colonia || '---' }}</p>
                </div>
                <div v-if="empleado?.codigo_postal" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Código Postal</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.codigo_postal || '---' }}</p>
                </div>
                <div v-if="empleado?.localidad" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Localidad</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.localidad || '---' }}</p>
                </div>
                <div v-if="empleado?.municipio_alcaldia" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Municipio / Alcaldía</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.municipio_alcaldia || '---' }}</p>
                </div>
                <div v-if="empleado?.estado" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Estado</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.estado || '---' }}</p>
                </div>
                <div v-if="empleado?.pais" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">País</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.pais || '---' }}</p>
                </div>
                <div v-if="empleado?.referencias" class="rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)] md:col-span-2" style="background-color: var(--color-bg)">
                  <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Referencias</p>
                  <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">{{ empleado.referencias || '---' }}</p>
                </div>
              </template>
            </div>
          </div>

          <div class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-4">
            <h3 class="text-lg font-semibold" style="color: var(--color-text)">Contraseña</h3>

            <div v-if="!editingPassword" class="flex items-center justify-between rounded-2xl p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]" style="background-color: var(--color-bg)">
              <div>
                <p class="text-xs opacity-60 uppercase tracking-wider" style="color: var(--color-text)">Contraseña</p>
                <p class="text-sm font-medium mt-0.5" style="color: var(--color-text)">••••••••</p>
              </div>
              <button @click="editingPassword = true" class="text-sm font-medium px-4 py-1.5 rounded-xl text-white transition-all" :style="{ backgroundColor: 'var(--color-primary)' }">
                Actualizar
              </button>
            </div>
            <div v-else class="space-y-3">
              <div>
                <label class="text-xs opacity-60 uppercase tracking-wider mb-1.5 block" style="color: var(--color-text)">Contraseña Actual</label>
                <input v-model="passwordForm.actual" type="password" class="w-full px-4 py-2.5 rounded-xl text-sm shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)] focus:outline-none" style="background-color: var(--color-bg); color: var(--color-text)" placeholder="••••••••" />
                <p v-if="passwordForm.errors.actual" class="text-sm text-red-500 mt-1">{{ passwordForm.errors.actual }}</p>
              </div>
              <div>
                <label class="text-xs opacity-60 uppercase tracking-wider mb-1.5 block" style="color: var(--color-text)">Nueva Contraseña</label>
                <input v-model="passwordForm.nueva" type="password" class="w-full px-4 py-2.5 rounded-xl text-sm shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)] focus:outline-none" style="background-color: var(--color-bg); color: var(--color-text)" placeholder="Nueva contraseña" />
              </div>
              <div>
                <label class="text-xs opacity-60 uppercase tracking-wider mb-1.5 block" style="color: var(--color-text)">Confirmar Nueva Contraseña</label>
                <input v-model="passwordForm.confirmar" type="password" class="w-full px-4 py-2.5 rounded-xl text-sm shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)] focus:outline-none" style="background-color: var(--color-bg); color: var(--color-text)" placeholder="Confirmar contraseña" />
                <p v-if="passwordForm.errors.confirmar" class="text-sm text-red-500 mt-1">{{ passwordForm.errors.confirmar }}</p>
              </div>
              <div class="flex items-center gap-2 pt-1">
                <button @click="cambiarPassword" :disabled="passwordForm.processing" class="text-sm font-medium px-5 py-2 rounded-xl text-white transition-all disabled:opacity-50" :style="{ backgroundColor: 'var(--color-primary)' }">
                  <span v-if="passwordForm.processing" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin mr-1 align-middle"></span>
                  Guardar Contraseña
                </button>
                <button @click="editingPassword = false; passwordForm.reset()" class="text-sm px-5 py-2 rounded-xl shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)]" style="color: var(--color-text)">
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

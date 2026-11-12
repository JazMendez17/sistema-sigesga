<script setup>
import { computed, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'

const props = defineProps({
  modulo: { type: String, required: true },
  titulo: { type: String, default: 'Importar desde Excel' },
  machoteUrl: { type: String, required: true },
  importarUrl: { type: String, required: true },
  hojas: { type: Object, required: true },
})

const nombresHojas = computed(() => Object.keys(props.hojas))
const hojaActiva = ref(nombresHojas.value[0])
watch(nombresHojas, (nuevas) => {
  if (!nuevas.includes(hojaActiva.value)) hojaActiva.value = nuevas[0]
})

const columnasHoja = computed(() => props.hojas[hojaActiva.value] || [])

const abierto = ref(false)
const archivo = ref(null)
const inputArchivo = ref(null)
const descartado = ref(false)

const form = useForm({ archivo: null })

const page = usePage()
const importacion = computed(() => page.props.flash?.importacion || null)
const resultado = computed(() => {
  if (!importacion.value || importacion.value.modulo !== props.modulo) return null
  if (descartado.value) return null
  return importacion.value
})

const nombreArchivo = computed(() => archivo.value?.name || '')

const totalColumnas = computed(() =>
  Object.values(props.hojas).reduce((suma, columnas) => suma + columnas.length, 0)
)

function seleccionArchivo(evento) {
  const elegido = evento.target.files?.[0] || null
  archivo.value = elegido
  form.archivo = elegido
  form.clearErrors('archivo')
}

function descargarMachote() {
  // @inertiajs/core 2.3.27 no expone router.download(); una navegación normal
  // basta porque el controlador responde Content-Disposition: attachment.
  const enlace = document.createElement('a')
  enlace.href = props.machoteUrl
  enlace.rel = 'noopener'
  document.body.appendChild(enlace)
  enlace.click()
  enlace.remove()
}

function importar() {
  if (!archivo.value) {
    form.setError('archivo', 'Selecciona primero el archivo Excel.')
    return
  }

  descartado.value = false
  form.post(props.importarUrl, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      abierto.value = false
      archivo.value = null
      form.archivo = null
      if (inputArchivo.value) inputArchivo.value.value = ''
    },
  })
}

function cierra() {
  abierto.value = false
  form.clearErrors()
}
</script>

<!-- Bloque de importación Excel: descarga del machote, carga del archivo y resumen de resultados -->
<template>
  <div class="space-y-4">
    <div class="flex flex-wrap items-center gap-3">
      <button
        @click="descargarMachote"
        class="inline-flex items-center gap-2 rounded-2xl bg-[#EEF2F7] px-4 py-2.5 text-sm font-medium text-[var(--color-text)] transition-all duration-200 shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] hover:text-[#4F46E5]"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" /></svg>
        Descargar machote
      </button>
      <NeumorphicButton variant="secondary" type="button" @click="abierto = true">
        Importar Excel
      </NeumorphicButton>
    </div>

    <!-- Resumen de la última importación -->
    <div
      v-if="resultado"
      class="rounded-3xl bg-[#EEF2F7] p-5 shadow-[6px_6px_12px_#d0d5da,-6px_-6px_12px_#ffffff]"
    >
      <div class="flex items-start justify-between gap-4">
        <div class="flex flex-wrap gap-4 text-sm">
          <span class="font-medium text-emerald-600">{{ resultado.creados }} creados</span>
          <span class="font-medium text-indigo-600">{{ resultado.actualizados }} actualizados</span>
          <span
            class="font-medium"
            :class="resultado.errores.length ? 'text-red-600' : 'text-gray-500'"
          >{{ resultado.errores.length }} con error</span>
        </div>
        <button @click="descartado = true" class="text-gray-400 transition-colors hover:text-gray-600">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>

      <ul v-if="resultado.errores.length" class="mt-4 max-h-48 space-y-1 overflow-y-auto text-xs text-red-600">
        <li v-for="(error, i) in resultado.errores" :key="i" class="flex gap-2">
          <span class="shrink-0 font-semibold">{{ error.hoja }}<template v-if="error.fila"> · fila {{ error.fila }}</template>:</span>
          <span>{{ error.mensaje }}</span>
        </li>
      </ul>
    </div>

    <Modal :show="abierto" max-width="2xl" @close="cierra">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-800">{{ titulo }}</h2>
        <p class="mt-1 text-sm text-gray-500">
          Descarga el machote, capture una fila por registro y sube el archivo. Se importan
          {{ totalColumnas }} campos en {{ nombresHojas.length }}
          {{ nombresHojas.length === 1 ? 'hoja' : 'hojas' }}.
        </p>

        <!-- Pestañas por hoja -->
        <div v-if="nombresHojas.length > 1" class="mt-4 flex flex-wrap gap-2">
          <button
            v-for="nombre in nombresHojas"
            :key="nombre"
            @click="hojaActiva = nombre"
            class="rounded-xl px-3 py-1.5 text-xs font-medium transition-all"
            :class="hojaActiva === nombre
              ? 'bg-[#EEF2F7] text-[#4F46E5] shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff]'
              : 'text-gray-500 hover:text-gray-700'"
          >
            {{ nombre }}
          </button>
        </div>

        <!-- Columnas requeridas por la hoja activa -->
        <div class="mt-4 max-h-64 overflow-y-auto rounded-2xl bg-[#F9FAFB] p-1">
          <table class="w-full text-left text-xs">
            <thead class="sticky top-0 bg-[#F9FAFB] text-gray-500">
              <tr>
                <th class="px-3 py-2 font-medium">Columna</th>
                <th class="px-3 py-2 font-medium">Obligatorio</th>
                <th class="px-3 py-2 font-medium">Valores / formato</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="columna in columnasHoja" :key="columna.campo">
                <td class="px-3 py-2 font-medium text-gray-700">
                  {{ columna.etiqueta }}
                  <span v-if="columna.requerido" class="text-red-500">*</span>
                </td>
                <td class="px-3 py-2 text-gray-500">{{ columna.requerido ? 'Sí' : 'No' }}</td>
                <td class="px-3 py-2 text-gray-500">
                  <span v-if="columna.opciones?.length">{{ columna.opciones.join(', ') }}</span>
                  <span v-else-if="columna.tipo === 'fecha'">aaaa-mm-dd</span>
                  <span v-else-if="columna.tipo === 'numero'">Número</span>
                  <span v-else-if="columna.tipo === 'booleano'">Sí / No</span>
                  <span v-else>Texto</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Selección del archivo -->
        <div class="mt-5">
          <label class="block cursor-pointer rounded-2xl border-2 border-dashed border-gray-300 px-4 py-6 text-center transition-colors hover:border-[#4F46E5]">
            <input
              ref="inputArchivo"
              type="file"
              accept=".xlsx,.xls"
              class="hidden"
              @change="seleccionArchivo"
            />
            <p class="text-sm font-medium text-gray-700">
              {{ nombreArchivo || 'Selecciona o arrastra el archivo Excel' }}
            </p>
            <p class="mt-1 text-xs text-gray-400">Formatos .xlsx o .xls · Máximo 5 MB</p>
          </label>
          <p v-if="form.errors.archivo" class="mt-2 text-xs text-red-600">{{ form.errors.archivo }}</p>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <NeumorphicButton variant="ghost" type="button" @click="cierra">Cancelar</NeumorphicButton>
          <NeumorphicButton
            variant="primary"
            type="button"
            :loading="form.processing"
            :disabled="!archivo || form.processing"
            @click="importar"
          >
            Subir e importar
          </NeumorphicButton>
        </div>
      </div>
    </Modal>
  </div>
</template>

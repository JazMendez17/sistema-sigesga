<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const page = usePage()
const empresaData = page.props.empresa || {}
const props = defineProps({
    nosotros: { type: Object, default: () => ({ quienes_somos: '', mision: '', vision: '' }) },
    valores: { type: Array, default: () => [] },
    servicios_landing: { type: Array, default: () => [] },
    modulo_colores: { type: Object, default: () => ({}) },
})

const activeTab = ref('general')
const fontOpen = ref(false)

const form = useForm({
  nombre: empresaData.nombre || '',
  siglas: empresaData.siglas || '',
  slogan: empresaData.slogan || '',
  texto_derechos: empresaData.texto_derechos || '',
  telefono_contacto: empresaData.telefono_contacto || '',
  email_contacto: empresaData.email_contacto || '',
  color_primario: empresaData.color_primario || '#4F46E5',
  color_secundario: empresaData.color_secundario || '#7C3AED',
  color_fondo: empresaData.color_fondo || '#E8EDF2',
  color_texto: empresaData.color_texto || '#1F2937',
  tipografia: empresaData.tipografia || 'Inter',
  modo_oscuro: empresaData.modo_oscuro || false,
  logo: empresaData.logo || '',
  imagen_fondo: empresaData.imagen_fondo || '',
})

const nosotros = reactive({ ...props.nosotros })
const valores = reactive(props.valores.map(v => ({ ...v })))
const serviciosLanding = reactive(props.servicios_landing.map(s => ({ ...s })))
const moduloColores = reactive({ ...props.modulo_colores })

function agregarValor() {
  valores.push({ valor: '', descripcion: '' })
}

function eliminarValor(i) {
  valores.splice(i, 1)
}

function agregarServicio() {
  serviciosLanding.push({ tipo: '', descripcion: '', foto: null })
}

function eliminarServicio(i) {
  serviciosLanding.splice(i, 1)
}

function uploadServicioFoto(index) {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/jpeg,image/png,image/webp'
  input.onchange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    const data = new FormData()
    data.append('file', file)
    data.append('type', 'servicio')
    router.post(route('panel.upload.store'), data, {
      preserveScroll: true,
      onSuccess: (page) => {
        serviciosLanding[index].foto = page.props.flash?.uploaded_path || file.name
      },
    })
  }
  input.click()
}

function guardarCambios() {
  const payload = {
    ...form.data(),
    nosotros: { ...nosotros },
    valores: valores.map(v => ({ valor: v.valor, descripcion: v.descripcion })),
    servicios_landing: serviciosLanding.map(s => ({ tipo: s.tipo, descripcion: s.descripcion, foto: s.foto })),
    modulo_colores: { ...moduloColores },
  }
  form.transform(() => payload).post(route('panel.configuracion.update'), {
    preserveScroll: true,
  })
}

const fonts = [
  'Inter', 'Roboto', 'Poppins', 'Montserrat', 'Nunito', 'Lato',
  'Open Sans', 'Raleway', 'Work Sans', 'Quicksand', 'Manrope',
  'DM Sans', 'Sora', 'Outfit', 'Plus Jakarta Sans', 'Jost',
  'Figtree', 'Lexend', 'Urbanist', 'Be Vietnam Pro', 'Space Grotesk',
  'Epilogue', 'Barlow', 'Rubik', 'Nunito Sans', 'Mulish',
  'Public Sans', 'Chakra Petch', 'Prompt', 'Karla', 'IBM Plex Sans',
  'Hanken Grotesk', 'Cabinet Grotesk', 'General Sans',
  'Clash Display', 'Satoshi', 'Switzer', 'Gambarino', 'Excon',
  'Sentient', 'Amulya', 'Boska', 'Gambetta', 'Synonym',
  'Archivo', 'Chillax', 'Supreme', 'Ambit',
  'Grostino', 'Khand', 'Chivo', 'Bricolage Grotesque', 'Familjen Grotesk',
  'Red Hat Display', 'Red Hat Text', 'Albert Sans', 'Readex Pro',
  'League Spartan', 'Fraunces', 'Ysabeau', 'Mohave', 'Crimson Pro',
  'Geist', 'Geist Mono', 'Georama', 'Instrument Sans', 'Instrument Serif',
  'Schibsted Grotesk', 'Sometype Mono', 'Wittgenstein', 'Zodiak',
]

function uploadFile(type) {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/jpeg,image/png,image/webp'
  input.onchange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    const data = new FormData()
    data.append('file', file)
    data.append('type', type)
    router.post(route('panel.upload.store'), data, {
      preserveScroll: true,
      onSuccess: (page) => {
        form[type] = page.props.flash?.uploaded_path || file.name
      },
    })
  }
  input.click()
}

const previewCard = { title: 'Vista Previa', content: 'Este es un ejemplo de cómo se verán los textos con la configuración actual.' }

function loadAllFonts() {
  const loaded = document.getElementById('sigesga-fonts-loaded')
  if (loaded) return
  const marker = document.createElement('span')
  marker.id = 'sigesga-fonts-loaded'
  marker.style.display = 'none'
  document.head.appendChild(marker)
  fonts.forEach(f => {
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = `https://fonts.googleapis.com/css2?family=${f.replace(/\s+/g, '+')}:wght@300;400;500;600;700;800&display=swap`
    document.head.appendChild(link)
  })
}

function toggleFontOpen() {
  fontOpen.value = !fontOpen.value
}

function onClickOutside(e) {
  const el = document.getElementById('font-selector')
  if (el && !el.contains(e.target)) fontOpen.value = false
}

onMounted(() => {
  document.addEventListener('mousedown', onClickOutside)
  loadAllFonts()
})
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside))
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Configuración de la Empresa</h1>
        <NeumorphicButton variant="primary" @click="guardarCambios" :disabled="form.processing">Guardar Cambios</NeumorphicButton>
      </div>

      <!-- Tabs -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="tab in ['general', 'apariencia', 'nosotros', 'servicios', 'modulos', 'contacto']"
          :key="tab"
          @click="activeTab = tab"
          class="rounded-xl px-5 py-2 text-sm font-medium capitalize transition-all duration-200"
          :class="activeTab === tab
            ? 'bg-[var(--color-surface)] text-[var(--color-primary)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]'
            : 'bg-transparent text-gray-500 hover:text-gray-700'"
        >
          {{ tab === 'nosotros' ? 'Nosotros' : tab === 'servicios' ? 'Servicios' : tab === 'modulos' ? 'Módulos' : tab }}
        </button>
      </div>

      <!-- General -->
      <div v-if="activeTab === 'general'" class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-5">
        <NeumorphicInput v-model="form.nombre" label="Nombre de la Empresa" icon="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
        <NeumorphicInput v-model="form.siglas" label="Siglas" icon="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        <NeumorphicInput v-model="form.slogan" label="Slogan" icon="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
        <div>
          <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Logo</label>
          <div class="flex items-center gap-4">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-[var(--color-bg)] shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
              <img v-if="form.logo" :src="'/storage/' + form.logo" class="h-full w-full rounded-2xl object-cover" @error="$event.target.style.display='none'" />
              <svg v-else class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
            <button @click="uploadFile('logo')" class="rounded-xl bg-[var(--color-bg)] px-4 py-2 text-sm text-gray-600 shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)] transition-all hover:text-gray-800">Seleccionar archivo</button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Imagen de Fondo</label>
          <div class="flex items-center gap-4">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-[var(--color-bg)] shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
              <img v-if="form.imagen_fondo" :src="'/storage/' + form.imagen_fondo" class="h-full w-full rounded-2xl object-cover" />
              <svg v-else class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
            <button @click="uploadFile('imagen_fondo')" class="rounded-xl bg-[var(--color-bg)] px-4 py-2 text-sm text-gray-600 shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)] transition-all hover:text-gray-800">Seleccionar archivo</button>
          </div>
        </div>
        <NeumorphicInput v-model="form.texto_derechos" label="Texto de Derechos" icon="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
      </div>

      <!-- Apariencia -->
      <div v-if="activeTab === 'apariencia'" class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div v-for="clave in ['primario', 'secundario', 'texto']" :key="clave" class="space-y-2">
            <label class="block text-sm font-medium text-[var(--color-text)] capitalize">{{ clave }}</label>
            <div class="flex items-center gap-3">
              <input type="color" :value="form['color_' + clave]" @input="form['color_' + clave] = $event.target.value" class="h-10 w-10 rounded-xl border-0 bg-transparent cursor-pointer" />
              <input
                :value="form['color_' + clave]"
                @input="form['color_' + clave] = $event.target.value"
                class="w-full rounded-2xl bg-[var(--color-bg)] px-4 py-2.5 text-sm text-[var(--color-text)] shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]"
              />
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between rounded-2xl bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
          <label class="text-sm font-medium text-[var(--color-text)]">Modo Oscuro</label>
          <button
            type="button"
            @click="form.modo_oscuro = !form.modo_oscuro"
            :class="form.modo_oscuro ? 'bg-[var(--color-primary)]' : 'bg-gray-300'"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-0 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]"
          >
            <span
              :class="form.modo_oscuro ? 'translate-x-6' : 'translate-x-1'"
              class="pointer-events-none relative inline-block h-4 w-4 translate-y-1 transform rounded-full bg-white shadow ring-0 transition duration-200"
            />
          </button>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-[var(--color-text)]">Tipografía</label>
          <div id="font-selector" class="relative">
            <button
              type="button"
              @click="toggleFontOpen()"
              class="flex w-full items-center justify-between rounded-2xl bg-[var(--color-bg)] px-4 py-3 text-sm text-[var(--color-text)] shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]"
              :style="{ fontFamily: form.tipografia }"
            >
              {{ form.tipografia }}
              <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': fontOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div
              v-if="fontOpen"
              class="absolute z-50 mt-2 max-h-56 w-full overflow-y-auto rounded-2xl bg-[var(--color-surface)] p-2 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]"
            >
              <button
                v-for="f in fonts"
                :key="f"
                type="button"
                @click="form.tipografia = f; fontOpen = false"
                class="flex w-full items-center rounded-xl px-4 py-2.5 text-left text-sm transition-colors hover:bg-[var(--color-bg)]"
                :class="form.tipografia === f ? 'bg-[var(--color-bg)] shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)]' : ''"
                :style="{ fontFamily: f }"
              >
                {{ f }}
              </button>
            </div>
          </div>
        </div>

        <div
          class="rounded-3xl p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)]"
          :style="{ backgroundColor: form.modo_oscuro ? '#1F2937' : '#E8EDF2', color: form.modo_oscuro ? '#F3F4F6' : form.color_texto, fontFamily: form.tipografia }"
        >
          <h3 class="text-lg font-bold">{{ previewCard.title }}</h3>
          <p class="mt-2 text-sm">{{ previewCard.content }}</p>
          <div class="mt-4 flex gap-3">
            <span class="rounded-xl px-4 py-2 text-sm font-medium text-white" :style="{ backgroundColor: form.color_primario }">Primario</span>
            <span class="rounded-xl px-4 py-2 text-sm font-medium text-white" :style="{ backgroundColor: form.color_secundario }">Secundario</span>
          </div>
        </div>
      </div>

      <!-- Nosotros -->
      <div v-if="activeTab === 'nosotros'" class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-5">
        <div class="space-y-2">
          <label class="block text-sm font-medium text-[var(--color-text)] capitalize">Quiénes Somos</label>
          <textarea
            v-model="nosotros.quienes_somos"
            rows="4"
            class="w-full rounded-2xl bg-[var(--color-bg)] px-4 py-3 text-sm text-[var(--color-text)] shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] resize-none"
          ></textarea>
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-medium text-[var(--color-text)] capitalize">Misión</label>
          <textarea
            v-model="nosotros.mision"
            rows="4"
            class="w-full rounded-2xl bg-[var(--color-bg)] px-4 py-3 text-sm text-[var(--color-text)] shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] resize-none"
          ></textarea>
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-medium text-[var(--color-text)] capitalize">Visión</label>
          <textarea
            v-model="nosotros.vision"
            rows="4"
            class="w-full rounded-2xl bg-[var(--color-bg)] px-4 py-3 text-sm text-[var(--color-text)] shadow-[inset_6px_6px_12px_var(--neumorphic-dark),inset_-6px_-6px_12px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] resize-none"
          ></textarea>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <label class="text-sm font-medium text-[var(--color-text)]">Valores</label>
            <button @click="agregarValor" class="rounded-xl bg-[var(--color-bg)] px-3 py-1.5 text-xs text-[var(--color-primary)] shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)] transition-all hover:text-[var(--color-primary)]">+ Agregar</button>
          </div>
          <div v-for="(item, i) in valores" :key="i" class="flex items-start gap-3 rounded-2xl bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
            <div class="flex-1 space-y-2">
              <input v-model="item.valor" placeholder="Valor" class="w-full rounded-xl bg-[var(--color-surface)] px-3 py-1.5 text-sm text-[var(--color-text)] shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
              <input v-model="item.descripcion" placeholder="Descripción" class="w-full rounded-xl bg-[var(--color-surface)] px-3 py-1.5 text-sm text-[var(--color-text)] shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
            </div>
            <button @click="eliminarValor(i)" class="mt-1 rounded-lg p-1.5 text-gray-400 hover:text-red-500 transition-colors">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Servicios (landing) -->
      <div v-if="activeTab === 'servicios'" class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-5">
        <div class="flex items-center justify-between">
          <label class="text-sm font-medium text-[var(--color-text)]">Servicios</label>
          <button @click="agregarServicio" class="rounded-xl bg-[var(--color-bg)] px-3 py-1.5 text-xs text-[var(--color-primary)] shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)] transition-all hover:text-[var(--color-primary)]">+ Agregar</button>
        </div>
        <div v-for="(item, i) in serviciosLanding" :key="i" class="rounded-2xl bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)] space-y-3">
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <input v-model="item.tipo" placeholder="Tipo de servicio" class="rounded-xl bg-[var(--color-surface)] px-3 py-1.5 text-sm text-[var(--color-text)] shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
            <input v-model="item.descripcion" placeholder="Descripción" class="rounded-xl bg-[var(--color-surface)] px-3 py-1.5 text-sm text-[var(--color-text)] shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] sm:col-span-1" />
            <div class="flex items-center gap-2">
              <button @click="uploadServicioFoto(i)" class="rounded-xl bg-[var(--color-surface)] px-3 py-1.5 text-xs text-gray-600 shadow-[3px_3px_6px_var(--neumorphic-dark),-3px_-3px_6px_var(--neumorphic-light)]">{{ item.foto ? 'Cambiar' : 'Foto' }}</button>
              <button @click="eliminarServicio(i)" class="rounded-lg p-1.5 text-gray-400 hover:text-red-500 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Módulos -->
      <div v-if="activeTab === 'modulos'" class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-5">
        <h3 class="text-sm font-medium text-[var(--color-text)] mb-4">Colores de Módulos</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="(color, modulo) in moduloColores" :key="modulo" class="space-y-2 rounded-2xl bg-[var(--color-bg)] p-4 shadow-[inset_4px_4px_8px_var(--neumorphic-dark),inset_-4px_-4px_8px_var(--neumorphic-light)]">
            <label class="block text-sm font-medium text-[var(--color-text)] capitalize">{{ modulo }}</label>
            <div class="flex items-center gap-3">
              <input type="color" :value="moduloColores[modulo]" @input="moduloColores[modulo] = $event.target.value" class="h-10 w-10 rounded-xl border-0 bg-transparent cursor-pointer" />
              <input :value="moduloColores[modulo]" @input="moduloColores[modulo] = $event.target.value" class="w-full rounded-xl bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-text)] shadow-[inset_3px_3px_6px_var(--neumorphic-dark),inset_-3px_-3px_6px_var(--neumorphic-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />
            </div>
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">Si no ves todos los módulos, guarda la configuración general primero para inicializarlos.</p>
      </div>

      <!-- Contacto -->
      <div v-if="activeTab === 'contacto'" class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-5">
        <NeumorphicInput v-model="form.telefono_contacto" label="Teléfono de Contacto" icon="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
        <NeumorphicInput v-model="form.email_contacto" label="Email de Contacto" icon="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
      </div>

      <div class="flex justify-end">
        <NeumorphicButton variant="primary" @click="guardarCambios" :disabled="form.processing">Guardar Cambios</NeumorphicButton>
      </div>
    </div>
  </AppLayout>
</template>

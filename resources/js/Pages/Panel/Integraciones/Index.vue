<script setup>
import { computed, ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import Badge from '@/Components/Badge.vue'

const page = usePage()
const integraciones = computed(() => page.props.integraciones || [])
const editando = ref(null)

const form = useForm({
  activo: true,
  public_key: '',
  api_key: '',
})

const iconos = {
  card: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
  whatsapp: 'M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z',
  mail: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
  map: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
}

function iconPath(item) {
  return iconos[item.icono] || 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
}

function toggleActivo(id, current) {
  router.put(route('panel.integraciones.update', { id }), { activo: !current }, { preserveScroll: true })
}

function abrirConfig(item) {
  editando.value = item
  form.activo = Boolean(item.activo)
  form.public_key = item.public_key || ''
  form.api_key = ''
}

function guardarConfig() {
  if (!editando.value) return
  form.put(route('panel.integraciones.update', { id: editando.value.id }), {
    preserveScroll: true,
    onSuccess: () => {
      editando.value = null
      form.reset()
    },
  })
}
</script>

<template>
  <!-- Panel exclusivo de Super Administrador para administrar API keys e integraciones -->
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Integraciones y Llaves API</h1>
        <p class="text-sm text-gray-500 mt-1">Sección exclusiva del Super Administrador para conectar pasarelas de pago y servicios externos.</p>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="item in integraciones"
          :key="item.id"
          class="rounded-3xl bg-[var(--color-surface)] p-6 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] space-y-4"
        >
          <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_var(--neumorphic-dark),-4px_-4px_8px_var(--neumorphic-light)]">
              <svg class="h-7 w-7 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="iconPath(item)" /></svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-800">{{ item.titulo }}</h3>
              <Badge :variant="item.configurado ? 'success' : 'neutral'">{{ item.configurado ? 'Configurado' : 'Sin configurar' }}</Badge>
            </div>
          </div>

          <p class="min-h-[2.5rem] text-sm text-gray-500">{{ item.descripcion || 'Integración con servicio externo.' }}</p>

          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Activo</span>
            <button
              type="button"
              @click="toggleActivo(item.id, item.activo)"
              class="relative h-7 w-12 rounded-full transition-colors duration-200"
              :class="item.activo ? 'bg-[var(--color-primary)]' : 'bg-gray-300'"
            >
              <span
                class="absolute left-0.5 top-0.5 h-6 w-6 rounded-full bg-white shadow transition-transform duration-200"
                :class="item.activo ? 'translate-x-5' : 'translate-x-0'"
              ></span>
            </button>
          </div>

          <NeumorphicButton type="button" variant="secondary" class="w-full" @click="abrirConfig(item)">
            Configurar credenciales
          </NeumorphicButton>
        </div>
      </div>

      <!-- Modal de credenciales -->
      <div v-if="editando" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="editando = null">
        <div class="bg-[#EEF2F7] rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
          <h2 class="text-lg font-bold text-gray-800">Configurar {{ editando.titulo }}</h2>

          <div v-if="editando.proveedor === 'mercadopago'">
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Public Key (Mercado Pago)</label>
            <input
              v-model="form.public_key"
              type="text"
              placeholder="APP_USR-... o TEST-..."
              class="w-full bg-[#E8EDF2] text-gray-800 rounded-2xl px-4 py-2.5 text-sm shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">
              {{ editando.proveedor === 'mercadopago' ? 'Access Token (privado)' : 'API Key' }}
            </label>
            <input
              v-model="form.api_key"
              type="password"
              :placeholder="editando.configurado ? 'Dejar vacío para mantener la actual' : 'Pega aquí tu llave privada'"
              class="w-full bg-[#E8EDF2] text-gray-800 rounded-2xl px-4 py-2.5 text-sm shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] focus:outline-none focus:ring-2 focus:ring-indigo-300"
            />
            <p class="text-[11px] text-gray-500 mt-1">La llave privada nunca se vuelve a mostrar por seguridad.</p>
          </div>

          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" v-model="form.activo" class="rounded border-gray-300 text-indigo-600" />
            Activar integración
          </label>

          <div class="flex gap-3 pt-2">
            <NeumorphicButton type="button" :loading="form.processing" @click="guardarConfig" class="flex-1">Guardar</NeumorphicButton>
            <NeumorphicButton type="button" variant="secondary" @click="editando = null">Cancelar</NeumorphicButton>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

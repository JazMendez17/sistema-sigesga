<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const props = defineProps({ acceso: Object })
const editing = !!props.acceso
const form = useForm({
  titulo: props.acceso?.titulo || '',
  descripcion: props.acceso?.descripcion || '',
  link: props.acceso?.link || '',
  imagen: null,
  icono: props.acceso?.icono || '',
  activo: props.acceso?.activo ?? true,
})

const preview = ref(props.acceso?.imagen || null)
const srcImagen = ref('')

function onArchivoSeleccionado(event) {
  const file = event.target.files[0]
  if (!file) return
  form.imagen = file
  srcImagen.value = URL.createObjectURL(file)
}

function submit() {
  const options = {
    onSuccess: () => form.reset(),
    forceFormData: true,
  }
  editing ? form.put(route('panel.accesos-rapidos.update', props.acceso.id), options) : form.post(route('panel.accesos-rapidos.store'), options)
}
</script>

<template>
  <AppLayout>
    <div class="max-w-3xl space-y-6">
      <div><h1 class="text-2xl font-bold text-gray-800">{{ editing ? 'Editar red social' : 'Agregar red social' }}</h1><p class="mt-1 text-sm text-gray-500">Configura la tarjeta que aparecerá en Accesos Rápidos.</p></div>
      <form class="neumorphic-card space-y-5 p-6" @submit.prevent="submit" enctype="multipart/form-data">
        <div><label class="mb-1 block text-sm font-medium">Nombre</label><NeumorphicInput v-model="form.titulo" placeholder="Instagram, Facebook, WhatsApp..." /></div>
        <div><label class="mb-1 block text-sm font-medium">Descripción</label><textarea v-model="form.descripcion" rows="3" class="w-full rounded-2xl bg-[var(--color-surface)] p-4 shadow-inner" placeholder="Texto que aparecerá debajo del nombre" /></div>
        <div><label class="mb-1 block text-sm font-medium">Enlace del sitio</label><NeumorphicInput v-model="form.link" type="url" placeholder="https://www.instagram.com/tu-cuenta" /></div>
        <div>
          <label class="mb-1 block text-sm font-medium">Imagen o icono</label>
          <div class="flex items-center gap-4">
            <img v-if="preview && !srcImagen" :src="preview" :alt="form.titulo" class="h-16 w-16 rounded-xl object-cover shadow-md" />
            <img v-else-if="srcImagen" :src="srcImagen" class="h-16 w-16 rounded-xl object-cover shadow-md" />
            <div class="flex-1">
              <label class="flex w-full cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl border-2 border-dashed border-[var(--neumorphic-dark)]/30 bg-[var(--color-surface)] px-4 py-6 text-center transition hover:border-[var(--color-primary)]">
                <input type="file" accept="image/*" class="absolute h-0 w-0 opacity-0" @change="onArchivoSeleccionado" />
                <span class="text-sm font-medium text-[var(--color-primary)]">{{ srcImagen ? 'Cambiar imagen' : 'Seleccionar imagen' }}</span>
                <span class="text-xs text-gray-500">{{ srcImagen ? form.imagen?.name : (preview ? 'Dejar la imagen actual o elegir otra' : 'JPG, PNG, WEBP o SVG · máx 2 MB') }}</span>
              </label>
            </div>
          </div>
        </div>
        <div><label class="flex items-center gap-2 text-sm"><input v-model="form.activo" type="checkbox" /> Visible en la landing</label></div>
        <div class="flex gap-3"><NeumorphicButton type="submit" :loading="form.processing">{{ editing ? 'Guardar cambios' : 'Agregar red social' }}</NeumorphicButton><NeumorphicButton variant="secondary" type="button" @click="router.visit(route('panel.accesos-rapidos.index'))">Cancelar</NeumorphicButton></div>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: #EEF2F7; border-radius: 24px; box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff; }
</style>

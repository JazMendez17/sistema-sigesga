<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Panel/AppLayout.vue'
import NeumorphicButton from '@/Components/NeumorphicButton.vue'
import NeumorphicInput from '@/Components/NeumorphicInput.vue'

const props = defineProps({ servicio: Object, coloresDisponibles: Array })
const editing = computed(() => !!props.servicio)
const imagePreview = ref(props.servicio?.foto ? `/storage/${props.servicio.foto}` : '')
const form = useForm({
  tipo: props.servicio?.tipo || '',
  descripcion: props.servicio?.descripcion || '',
  foto: null,
  orden: props.servicio?.orden || 0,
  activo: props.servicio?.activo ?? true,
  color: props.servicio?.color || props.coloresDisponibles?.[0]?.valor || '',
})

function selectImage(event) {
  const file = event.target.files[0]
  form.foto = file || null
  if (file) imagePreview.value = URL.createObjectURL(file)
}

function submit() {
  const options = { forceFormData: true, onSuccess: () => form.reset('foto') }
  editing.value ? form.put(route('panel.servicios-landing.update', props.servicio.id), options) : form.post(route('panel.servicios-landing.store'), options)
}
</script>

<template>
  <AppLayout>
    <div class="max-w-3xl space-y-6"><div><h1 class="text-2xl font-bold text-gray-800">{{ editing ? 'Editar servicio' : 'Agregar servicio' }}</h1><p class="mt-1 text-sm text-gray-500">Nombre, imagen y descripción son obligatorios.</p></div>
      <form class="neumorphic-card space-y-5 p-6" @submit.prevent="submit">
        <div><label class="mb-1 block text-sm font-medium">Nombre del servicio</label><NeumorphicInput v-model="form.tipo" placeholder="Grúa ligera" /></div>
        <div><label class="mb-1 block text-sm font-medium">Descripción</label><textarea v-model="form.descripcion" required rows="4" class="w-full rounded-2xl bg-[var(--color-surface)] p-4 shadow-inner" placeholder="Describe el servicio" /></div>
        <div><label class="mb-1 block text-sm font-medium">Imagen del servicio</label><input type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl p-3 text-sm shadow-inner" @change="selectImage" /><img v-if="imagePreview" :src="imagePreview" alt="Vista previa" class="mt-4 h-40 w-full rounded-2xl object-cover" /><p class="mt-1 text-xs text-gray-500">JPG, PNG o WEBP. Máximo 5 MB.</p></div>
        <div><label class="mb-2 block text-sm font-medium">Color del servicio</label><div class="grid gap-3 sm:grid-cols-2"><label v-for="color in coloresDisponibles" :key="color.valor" class="flex cursor-pointer items-center gap-3 rounded-xl p-3 shadow-md" :class="form.color === color.valor ? 'ring-2 ring-[var(--color-primary)]' : ''"><input v-model="form.color" type="radio" :value="color.valor" /><span class="h-7 w-7 rounded-full" :style="{ backgroundColor: color.valor }"></span><span class="text-sm">{{ color.nombre }}</span></label></div></div>
        <div class="grid gap-5 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-medium">Orden</label><NeumorphicInput v-model="form.orden" type="number" min="0" /></div><label class="flex items-center gap-2 pt-7 text-sm"><input v-model="form.activo" type="checkbox" /> Mostrar en la landing</label></div>
        <div class="flex gap-3"><NeumorphicButton type="submit" :loading="form.processing">{{ editing ? 'Guardar cambios' : 'Agregar servicio' }}</NeumorphicButton><NeumorphicButton type="button" variant="secondary" @click="router.visit(route('panel.servicios-landing.index'))">Cancelar</NeumorphicButton></div>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: #EEF2F7; border-radius: 24px; box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff; }
</style>

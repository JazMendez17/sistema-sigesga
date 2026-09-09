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
  imagen: props.acceso?.imagen || '',
  icono: props.acceso?.icono || '',
  orden: props.acceso?.orden || 0,
  activo: props.acceso?.activo ?? true,
})

function submit() {
  const options = { onSuccess: () => form.reset() }
  editing ? form.put(route('panel.accesos-rapidos.update', props.acceso.id), options) : form.post(route('panel.accesos-rapidos.store'), options)
}
</script>

<template>
  <AppLayout>
    <div class="max-w-3xl space-y-6">
      <div><h1 class="text-2xl font-bold text-gray-800">{{ editing ? 'Editar red social' : 'Agregar red social' }}</h1><p class="mt-1 text-sm text-gray-500">Configura la tarjeta que aparecerá en Accesos Rápidos.</p></div>
      <form class="neumorphic-card space-y-5 p-6" @submit.prevent="submit">
        <div><label class="mb-1 block text-sm font-medium">Nombre</label><NeumorphicInput v-model="form.titulo" placeholder="Instagram, Facebook, WhatsApp..." /></div>
        <div><label class="mb-1 block text-sm font-medium">Descripción</label><textarea v-model="form.descripcion" rows="3" class="w-full rounded-2xl bg-[var(--color-surface)] p-4 shadow-inner" placeholder="Texto que aparecerá debajo del nombre" /></div>
        <div><label class="mb-1 block text-sm font-medium">Enlace del sitio</label><NeumorphicInput v-model="form.link" type="url" placeholder="https://www.instagram.com/tu-cuenta" /></div>
        <div><label class="mb-1 block text-sm font-medium">URL de imagen o icono</label><NeumorphicInput v-model="form.imagen" type="url" placeholder="https://.../icono.png" /></div>
        <div class="grid gap-5 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-medium">Orden</label><NeumorphicInput v-model="form.orden" type="number" min="0" /></div><label class="flex items-center gap-2 pt-7 text-sm"><input v-model="form.activo" type="checkbox" /> Visible en la landing</label></div>
        <div class="flex gap-3"><NeumorphicButton type="submit" :loading="form.processing">{{ editing ? 'Guardar cambios' : 'Agregar red social' }}</NeumorphicButton><NeumorphicButton variant="secondary" type="button" @click="router.visit(route('panel.accesos-rapidos.index'))">Cancelar</NeumorphicButton></div>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.neumorphic-card { background: #EEF2F7; border-radius: 24px; box-shadow: 8px 8px 16px #d0d5da, -8px -8px 16px #ffffff; }
</style>

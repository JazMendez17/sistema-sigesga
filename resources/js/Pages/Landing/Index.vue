<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme'

useTheme()

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
})

const empresa = computed(() => usePage().props.empresa || {})
const mobileMenuOpen = ref(false)

const cssVars = computed(() => ({
    '--color-primary': empresa.value.color_primario || '#4F46E5',
    '--color-secondary': empresa.value.color_secundario || '#7C3AED',
    '--color-accent': empresa.value.color_secundario || '#d97706',
    '--font-family': empresa.value.tipografia ? `"${empresa.value.tipografia}", sans-serif` : 'Roboto, sans-serif',
}))

onMounted(() => {
    const font = empresa.value.tipografia
    if (font) {
        const link = document.createElement('link')
        link.href = `https://fonts.googleapis.com/css2?family=${font.replace(/ /g, '+')}:wght@300;400;500;600;700;800&display=swap`
        link.rel = 'stylesheet'
        document.head.appendChild(link)
    }
})

const scrollTo = (id) => {
    mobileMenuOpen.value = false
    const el = document.getElementById(id)
    if (el) el.scrollIntoView({ behavior: 'smooth' })
}

const navLinks = [
    { label: 'Inicio', id: 'inicio' },
    { label: 'Nosotros', id: 'nosotros' },
    { label: 'Servicio', id: 'servicio' },
    { label: 'Contacto', id: 'contacto' },
]

const valores = [
    { titulo: 'Honestidad', descripcion: 'Actuamos con transparencia y ética en cada servicio que brindamos.', icono: 'shield' },
    { titulo: 'Responsabilidad', descripcion: 'Cumplimos nuestros compromisos con puntualidad y seriedad.', icono: 'check' },
    { titulo: 'Compromiso', descripcion: 'Damos lo mejor de nosotros para satisfacer a cada cliente.', icono: 'heart' },
    { titulo: 'Calidad', descripcion: 'Ofrecemos servicios con los más altos estándares de calidad.', icono: 'star' },
    { titulo: 'Innovación', descripcion: 'Nos mantenemos a la vanguardia con tecnología y procesos modernos.', icono: 'bulb' },
    { titulo: 'Seguridad', descripcion: 'Priorizamos la integridad de nuestros clientes y sus bienes.', icono: 'users' },
]

const servicios = [
    { tipo: 'Grúa Ligera', descripcion: 'Servicio de grúa para vehículos compactos y medianos. Rápido y seguro.', color: '#2563eb' },
    { tipo: 'Grúa Pesada', descripcion: 'Grúa de gran capacidad para camiones, autobuses y maquinaria pesada.', color: '#7c3aed' },
    { tipo: 'Asistencia Vial', descripcion: 'Asistencia en carretera las 24 horas del día, los 365 días del año.', color: '#059669' },
    { tipo: 'Cambio de Llantas', descripcion: 'Cambio de neumáticos en el lugar donde te encuentres, sin demoras.', color: '#d97706' },
    { tipo: 'Carga de Batería', descripcion: 'Servicio de carga o sustitución de batería para cualquier vehículo.', color: '#dc2626' },
    { tipo: 'Cerrajería Automotriz', descripcion: 'Apertura de vehículos sin dañar la cerradura. Profesional y rápido.', color: '#0891b2' },
]

const accesos = [
    { titulo: 'Solicitar Servicio', descripcion: 'Solicita una grúa o asistencia de forma rápida y sencilla.', enlace: '/solicitar', icono: 'clipboard' },
    { titulo: 'Rastrear Servicio', descripcion: 'Da seguimiento en tiempo real a tu solicitud de servicio.', enlace: '/rastrear', icono: 'search' },
    { titulo: 'Facturación', descripcion: 'Consulta y paga tus facturas en línea de manera segura.', enlace: '/login', icono: 'document' },
    { titulo: 'Soporte', descripcion: 'Comunícate con nuestro equipo de soporte técnico.', enlace: '/soporte', icono: 'chat' },
]

const form = reactive({
    nombre: '',
    email: '',
    mensaje: '',
})

const submitContacto = () => {
    if (!form.nombre || !form.email || !form.mensaje) return
    fetch('/contacto', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        body: JSON.stringify({ ...form }),
    })
    form.nombre = ''
    form.email = ''
    form.mensaje = ''
}

const flash = computed(() => usePage().props.flash || {})

const icons = {
    shield: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>',
    heart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
    star: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>',
    check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    bulb: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>',
    users: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
    truck: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 17h8M8 17a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 104 0 2 2 0 00-4 0zm-9-3V5a1 1 0 011-1h9a1 1 0 011 1v9M3 14h1m16 0h1M5 10h14"/></svg>',
    wrench: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11.42 15.17l-7.5 7.5a2 2 0 01-2.83-2.83l7.5-7.5m2.83 2.83a6 6 0 11-2.83-2.83zm0 0l5.66-5.66M14.24 9.24l5.66-5.66M14.24 9.24A6 6 0 0112 4.24M17.66 6.34L20 4"/></svg>',
    battery: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="6" width="18" height="12" rx="2" ry="2"/><line x1="23" y1="10" x2="23" y2="14"/><line x1="7" y1="10" x2="7" y2="14"/><line x1="11" y1="10" x2="11" y2="14"/><line x1="15" y1="10" x2="15" y2="14"/></svg>',
    key: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 7a4 4 0 11-8 0 4 4 0 018 0zm0 0l4 4m-4-4l4-4m-4 4l-4 4"/><path d="M7 11l-3 3m0 0l3 3m-3-3h8"/></svg>',
    clipboard: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6"/><path d="M9 12l2 2 4-4"/></svg>',
    search: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>',
    document: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
    chat: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>',
    phone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>',
    mail: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
    pin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    menu: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
    x: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    arrowRight: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
}

const getIcon = (name) => icons[name] || ''
</script>

<template>
    <!-- Landing page principal con secciones: inicio, nosotros, servicio, contacto -->
    <Head :title="empresa.nombre || 'SIGESGA'" />

    <div :style="cssVars" class="bg-[var(--color-bg)] min-h-screen" style="color: var(--color-text)">
        <div v-if="flash.success" class="fixed top-4 right-4 z-[100] max-w-sm p-4 rounded-2xl bg-emerald-50 text-emerald-800 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] flex items-center gap-3 animate-[slideIn_0.3s_ease-out]">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium">{{ flash.success }}</p>
            <button @click="flash.success = null" class="ml-auto text-emerald-600 hover:text-emerald-800">&times;</button>
        </div>
        <div v-if="flash.error" class="fixed top-4 right-4 z-[100] max-w-sm p-4 rounded-2xl bg-red-50 text-red-800 shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] flex items-center gap-3 animate-[slideIn_0.3s_ease-out]">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <p class="text-sm font-medium">{{ flash.error }}</p>
            <button @click="flash.error = null" class="ml-auto text-red-600 hover:text-red-800">&times;</button>
        </div>
        <nav class="fixed top-0 left-0 right-0 z-50 bg-[var(--color-bg)] shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] transition-all duration-300 py-3 px-4 md:px-8">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="#inicio" @click.prevent="scrollTo('inicio')" class="flex items-center gap-3 group">
                    <div v-if="empresa.logo" class="w-10 h-10 rounded-2xl overflow-hidden shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]">
                        <img :src="'/storage/' + empresa.logo" :alt="empresa.nombre" class="w-full h-full object-contain" />
                    </div>
                    <div v-else class="w-10 h-10 rounded-2xl bg-[var(--color-bg)] flex items-center justify-center text-lg font-bold shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]"
                         :style="{ color: 'var(--color-primary)' }">
                        {{ (empresa.siglas || empresa.nombre || 'SG').charAt(0) }}
                    </div>
                    <span class="font-semibold text-lg hidden sm:block" :style="{ color: 'var(--color-primary)' }">
                        {{ empresa.siglas || empresa.nombre || 'SIGESGA' }}
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-1">
                    <a v-for="link in navLinks" :key="link.id"
                       :href="`#${link.id}`"
                       @click.prevent="scrollTo(link.id)"
                       class="px-4 py-2 rounded-2xl text-sm font-medium transition-all duration-200 hover:shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] text-[var(--color-text)]">
                        {{ link.label }}
                    </a>
                </div>

<div class="hidden md:flex items-center gap-3">
    <a href="/login"
       class="px-5 py-2 rounded-2xl text-sm font-medium transition-all duration-200 shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] hover:shadow-[6px_6px_12px_#c9ced3,-6px_-6px_12px_#ffffff]"
       :style="{ color: 'var(--color-primary)' }">
        Iniciar Sesión
    </a>
    <a href="/register"
       class="px-5 py-2 rounded-2xl text-sm font-semibold text-white transition-all duration-200 shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] hover:shadow-[6px_6px_12px_#c9ced3,-6px_-6px_12px_#ffffff]"
       :style="{ backgroundColor: 'var(--color-primary)' }">
        Registrarse
    </a>
</div>

                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden p-2 rounded-2xl shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] text-[var(--color-text)] transition-all duration-200">
                    <span class="w-6 h-6 block" v-html="getIcon(mobileMenuOpen ? 'x' : 'menu')"></span>
                </button>
            </div>

            <div v-if="mobileMenuOpen"
                 class="md:hidden mt-3 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] p-4 space-y-2">
                <a v-for="link in navLinks" :key="link.id"
                   :href="`#${link.id}`"
                   @click.prevent="scrollTo(link.id)"
                   class="block px-4 py-3 rounded-2xl text-sm font-medium text-[var(--color-text)] hover:shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] transition-all duration-200">
                    {{ link.label }}
                </a>
                <hr class="border-gray-300 my-2" />
                <a href="/login"
                   class="block w-full px-4 py-3 rounded-2xl text-sm font-medium text-center transition-all duration-200 shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]"
                   :style="{ color: 'var(--color-primary)' }">
                    Iniciar Sesión
                </a>
                <a href="/register"
                   class="block w-full px-4 py-3 rounded-2xl text-sm font-semibold text-white text-center transition-all duration-200 shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff]"
                   :style="{ backgroundColor: 'var(--color-primary)' }">
                    Registrarse
                </a>
            </div>
        </nav>

        <section id="inicio" class="relative min-h-screen flex items-center overflow-hidden">
            <div v-if="empresa.imagen_fondo" class="absolute inset-0">
                <img :src="'/storage/' + empresa.imagen_fondo" alt="" class="w-full h-full object-cover" />
            </div>
            <div v-else class="absolute inset-0 bg-gradient-to-br from-[#1e3a5f] via-[#2d5a87] to-[#1e3a5f]"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-black/30"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#E8EDF2] via-transparent to-transparent opacity-30"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 md:px-8 w-full">
                <div class="max-w-3xl">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                        Sistema Integral de Gestión de Servicios de Grúas y Asistencia Vial
                    </h1>
                    <p v-if="empresa.slogan" class="text-xl md:text-2xl text-white/80 mb-10 font-light">
                        {{ empresa.slogan }}
                    </p>
                    <a href="/register"
                       class="inline-flex items-center gap-2 px-8 py-4 rounded-3xl text-lg font-semibold text-white transition-all duration-300 shadow-[8px_8px_16px_#00000033,-8px_-8px_16px_#ffffff1a] hover:shadow-[10px_10px_20px_#0000004d,-10px_-10px_20px_#ffffff33] hover:scale-[1.02]"
                       :style="{ backgroundColor: 'var(--color-primary)' }">
                        Comenzar
                        <span class="w-5 h-5" v-html="getIcon('arrowRight')"></span>
                    </a>
                </div>
            </div>

            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 animate-bounce">
                <a href="#nosotros" @click.prevent="scrollTo('nosotros')" class="text-white/60 hover:text-white transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </a>
            </div>
        </section>

        <section id="nosotros" class="py-20 md:py-28 px-4 md:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4" :style="{ color: 'var(--color-primary)' }">Nosotros</h2>
                    <div class="w-20 h-1 rounded-full mx-auto" :style="{ backgroundColor: 'var(--color-primary)' }"></div>
                </div>

                <div class="grid md:grid-cols-3 gap-8 mb-20">
                    <div class="p-8 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] transition-all duration-300 hover:shadow-[12px_12px_24px_#c9ced3,-12px_-12px_24px_#ffffff]">
                        <h3 class="text-xl font-bold mb-4" :style="{ color: 'var(--color-primary)' }">Quiénes Somos</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Somos una empresa dedicada a la prestación de servicios de grúas y asistencia vial, comprometidos con la calidad y la satisfacción de nuestros clientes. Contamos con un equipo profesional y una flota moderna para atender cualquier emergencia en la vía.
                        </p>
                    </div>
                    <div class="p-8 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] transition-all duration-300 hover:shadow-[12px_12px_24px_#c9ced3,-12px_-12px_24px_#ffffff]">
                        <h3 class="text-xl font-bold mb-4" :style="{ color: 'var(--color-primary)' }">Misión</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Brindar servicios de grúas y asistencia vial con rapidez, seguridad y calidad, superando las expectativas de nuestros clientes y contribuyendo al bienestar de la comunidad.
                        </p>
                    </div>
                    <div class="p-8 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] transition-all duration-300 hover:shadow-[12px_12px_24px_#c9ced3,-12px_-12px_24px_#ffffff]">
                        <h3 class="text-xl font-bold mb-4" :style="{ color: 'var(--color-primary)' }">Visión</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Ser la empresa líder en servicios de grúas y asistencia vial a nivel nacional, reconocida por nuestra excelencia operativa, innovación tecnológica y compromiso con el cliente.
                        </p>
                    </div>
                </div>

                <div class="mb-20">
                    <h3 class="text-2xl font-bold text-center mb-4" :style="{ color: 'var(--color-primary)' }">Nuestros Valores</h3>
                    <p class="text-gray-500 text-center mb-10 max-w-2xl mx-auto">Los principios que guían cada uno de nuestros servicios y decisiones.</p>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="val in valores" :key="val.titulo"
                             class="p-6 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] transition-all duration-300 hover:shadow-[12px_12px_24px_#c9ced3,-12px_-12px_24px_#ffffff] group">
                            <div class="w-12 h-12 rounded-2xl bg-[var(--color-bg)] shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff] flex items-center justify-center mb-4 group-hover:shadow-[inset_6px_6px_12px_#c9ced3,inset_-6px_-6px_12px_#ffffff] transition-all duration-300"
                                 :style="{ color: 'var(--color-primary)' }">
                                <span class="w-6 h-6" v-html="getIcon(val.icono)"></span>
                            </div>
                            <h4 class="text-lg font-bold mb-2 text-gray-800">{{ val.titulo }}</h4>
                            <p class="text-sm text-gray-500 leading-relaxed">{{ val.descripcion }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-2xl font-bold text-center mb-4" :style="{ color: 'var(--color-primary)' }">Accesos Rápidos</h3>
                    <p class="text-gray-500 text-center mb-10 max-w-2xl mx-auto">Herramientas y servicios disponibles para ti.</p>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="acceso in accesos" :key="acceso.titulo"
                             class="p-6 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] transition-all duration-300 hover:shadow-[12px_12px_24px_#c9ced3,-12px_-12px_24px_#ffffff] group text-center">
                            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 bg-[var(--color-bg)] shadow-[6px_6px_12px_#d0d5da,-6px_-6px_12px_#ffffff] flex items-center justify-center transition-all duration-300 group-hover:shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff]"
                                 :style="{ color: 'var(--color-primary)' }">
                                <span class="w-7 h-7" v-html="getIcon(acceso.icono)"></span>
                            </div>
                            <h4 class="text-base font-bold mb-2 text-gray-800">{{ acceso.titulo }}</h4>
                            <p class="text-sm text-gray-500 mb-4">{{ acceso.descripcion }}</p>
                            <a :href="acceso.enlace"
                               class="inline-flex items-center gap-1 text-sm font-semibold transition-all duration-200 hover:gap-2"
                               :style="{ color: 'var(--color-primary)' }">
                                Acceder
                                <span class="w-4 h-4" v-html="getIcon('arrowRight')"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="servicio" class="py-20 md:py-28 px-4 md:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4" :style="{ color: 'var(--color-primary)' }">Servicio</h2>
                    <div class="w-20 h-1 rounded-full mx-auto" :style="{ backgroundColor: 'var(--color-primary)' }"></div>
                    <p class="text-gray-500 mt-4 max-w-2xl mx-auto">Ofrecemos una amplia gama de servicios de grúas y asistencia vial para cubrir todas tus necesidades.</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(svc, idx) in servicios" :key="svc.tipo"
                         class="rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff] overflow-hidden transition-all duration-300 hover:shadow-[12px_12px_24px_#c9ced3,-12px_-12px_24px_#ffffff] group">
                        <div class="h-48 flex items-center justify-center relative overflow-hidden"
                             :style="{ background: `linear-gradient(135deg, ${svc.color}22, ${svc.color}44)` }">
                            <div class="w-24 h-24 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#0000001a,-8px_-8px_16px_#ffffff66] flex items-center justify-center transition-all duration-300 group-hover:scale-110"
                                 :style="{ color: svc.color }">
                                <span class="w-12 h-12" v-html="getIcon(idx === 0 || idx === 1 ? 'truck' : idx === 2 ? 'wrench' : idx === 3 ? 'key' : idx === 4 ? 'battery' : 'key')"></span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2" :style="{ color: svc.color }">{{ svc.tipo }}</h3>
                            <p class="text-gray-500 text-sm mb-5 leading-relaxed">{{ svc.descripcion }}</p>
                            <a href="/register"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-semibold text-white transition-all duration-200 shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] hover:shadow-[6px_6px_12px_#c9ced3,-6px_-6px_12px_#ffffff]"
                               :style="{ backgroundColor: svc.color }">
                                Solicitar
                                <span class="w-4 h-4" v-html="getIcon('arrowRight')"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contacto" class="py-20 md:py-28 px-4 md:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4" :style="{ color: 'var(--color-primary)' }">Contacto</h2>
                    <div class="w-20 h-1 rounded-full mx-auto" :style="{ backgroundColor: 'var(--color-primary)' }"></div>
                    <p class="text-gray-500 mt-4 max-w-2xl mx-auto">Estamos aquí para ayudarte. Contáctanos por cualquier medio.</p>
                </div>

                <div class="grid lg:grid-cols-2 gap-12">
                    <div class="space-y-8">
                        <div class="p-8 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
                            <h3 class="text-xl font-bold mb-6" :style="{ color: 'var(--color-primary)' }">Información de Contacto</h3>
                            <div class="space-y-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] flex items-center justify-center shrink-0"
                                         :style="{ color: 'var(--color-primary)' }">
                                        <span class="w-5 h-5" v-html="getIcon('phone')"></span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wider font-medium">Teléfono</p>
                                        <p class="text-[var(--color-text)] font-medium">{{ empresa.telefono || '(555) 123-4567' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] flex items-center justify-center shrink-0"
                                         :style="{ color: 'var(--color-primary)' }">
                                        <span class="w-5 h-5" v-html="getIcon('mail')"></span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wider font-medium">Email</p>
                                        <p class="text-[var(--color-text)] font-medium">{{ empresa.email || 'contacto@sigesga.com' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-[var(--color-bg)] shadow-[4px_4px_8px_#d0d5da,-4px_-4px_8px_#ffffff] flex items-center justify-center shrink-0"
                                         :style="{ color: 'var(--color-primary)' }">
                                        <span class="w-5 h-5" v-html="getIcon('pin')"></span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wider font-medium">Ubicación</p>
                                        <p class="text-[var(--color-text)] font-medium">{{ empresa.direccion || 'Av. Principal #123, Ciudad' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
                            <h3 class="text-xl font-bold mb-6" :style="{ color: 'var(--color-primary)' }">Oficinas</h3>
                            <div class="space-y-4">
                                <div class="p-4 rounded-2xl bg-[var(--color-bg)] shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff]">
                                    <p class="font-semibold text-gray-800">Oficina Central</p>
                                    <p class="text-sm text-gray-500">Blvd. Corporativo 500, Col. Centro</p>
                                </div>
                                <div class="p-4 rounded-2xl bg-[var(--color-bg)] shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff]">
                                    <p class="font-semibold text-gray-800">Sucursal Norte</p>
                                    <p class="text-sm text-gray-500">Av. Industrial 1500, Zona Norte</p>
                                </div>
                                <div class="p-4 rounded-2xl bg-[var(--color-bg)] shadow-[inset_4px_4px_8px_#d0d5da,inset_-4px_-4px_8px_#ffffff]">
                                    <p class="font-semibold text-gray-800">Sucursal Sur</p>
                                    <p class="text-sm text-gray-500">Carr. Nacional Km 15, Zona Sur</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
                        <h3 class="text-xl font-bold mb-6" :style="{ color: 'var(--color-primary)' }">Envíanos un Mensaje</h3>
                        <form @submit.prevent="submitContacto" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-2">Nombre</label>
                                <input v-model="form.nombre" type="text" required
                                       class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none transition-all duration-200"
                                       placeholder="Tu nombre completo" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-2">Email</label>
                                <input v-model="form.email" type="email" required
                                       class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none transition-all duration-200"
                                       placeholder="tu@correo.com" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-2">Mensaje</label>
                                <textarea v-model="form.mensaje" required rows="5"
                                          class="w-full px-5 py-3.5 rounded-2xl bg-[var(--color-bg)] shadow-[inset_6px_6px_12px_#d0d5da,inset_-6px_-6px_12px_#ffffff] text-[var(--color-text)] placeholder-gray-400 focus:outline-none transition-all duration-200 resize-none"
                                          placeholder="Escribe tu mensaje aquí..."></textarea>
                            </div>
                            <button type="submit"
                                    class="w-full py-3.5 rounded-2xl text-sm font-semibold text-white transition-all duration-200 shadow-[6px_6px_12px_#d0d5da,-6px_-6px_12px_#ffffff] hover:shadow-[8px_8px_16px_#c9ced3,-8px_-8px_16px_#ffffff] hover:scale-[1.01] active:scale-[0.99]"
                                    :style="{ backgroundColor: 'var(--color-primary)' }">
                                Enviar Mensaje
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-8 px-4 md:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="p-8 rounded-3xl bg-[var(--color-bg)] shadow-[8px_8px_16px_#d0d5da,-8px_-8px_16px_#ffffff]">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-[var(--color-bg)] shadow-[3px_3px_6px_#d0d5da,-3px_-3px_6px_#ffffff] flex items-center justify-center text-xs font-bold"
                                 :style="{ color: 'var(--color-primary)' }">
                                {{ (empresa.siglas || empresa.nombre || 'SG').charAt(0) }}
                            </div>
                            <span class="text-sm font-semibold" :style="{ color: 'var(--color-primary)' }">
                                {{ empresa.siglas || empresa.nombre || 'SIGESGA' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 text-center">
                            {{ empresa.texto_derechos || `© ${new Date().getFullYear()} SIGESGA. Todos los derechos reservados.` }}
                        </p>
                        <div class="flex items-center gap-4 text-xs text-gray-400">
                            <a href="#inicio" @click.prevent="scrollTo('inicio')" class="hover:text-gray-600 transition-colors">Inicio</a>
                            <a href="#nosotros" @click.prevent="scrollTo('nosotros')" class="hover:text-gray-600 transition-colors">Nosotros</a>
                            <a href="#servicio" @click.prevent="scrollTo('servicio')" class="hover:text-gray-600 transition-colors">Servicio</a>
                            <a href="#contacto" @click.prevent="scrollTo('contacto')" class="hover:text-gray-600 transition-colors">Contacto</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
html {
    scroll-behavior: smooth;
}
body {
    background-color: var(--color-bg);
}
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

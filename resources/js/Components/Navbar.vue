<script setup>
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const empresa = computed(() => page.props.empresa)
const menuOpen = ref(false)
const userMenuOpen = ref(false)
const activeMega = ref(null)
let megaTimeout = null

function logout() {
  router.post(route('logout'))
}

function canShow(item) {
  if (!item.roles) return true
  if (!user.value) return false
  return item.roles.includes(user.value.rol)
}

function isGroupVisible(g) {
  if (!g.roles) return true
  if (!user.value) return false
  return g.roles.includes(user.value.rol)
}

function isGroupActive(g) {
  return g.items.some(item => route().current(item.route))
}

function isItemActive(item) {
  return route().current(item.route)
}

function getVisibleItems(g) {
  return g.items.filter(canShow)
}

function onMegaEnter(title) {
  clearTimeout(megaTimeout)
  activeMega.value = title
}

function onMegaLeave() {
  megaTimeout = setTimeout(() => {
    activeMega.value = null
  }, 80)
}

const menuGroups = computed(() => [
  {
    title: 'Principal',
    items: [
      { label: 'Dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', route: 'panel.dashboard', roles: ['admin', 'cotizador', 'operador', 'cliente'] },
    ],
  },
  {
    title: 'Operación',
    roles: ['admin', 'cotizador', 'operador'],
    items: [
      { label: 'Cotizaciones', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', route: 'panel.cotizaciones.index', roles: ['admin', 'cotizador'] },
      { label: 'Servicios', icon: 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1m3 1V9m4 8l2-1m-8-4h.01', route: 'panel.servicios.index', roles: ['admin', 'cotizador', 'operador'] },
      { label: 'Autorizaciones', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z', route: 'panel.autorizaciones-cancelacion.index', roles: ['admin', 'cotizador', 'operador'] },
      { label: 'Facturación', icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z', route: 'panel.facturacion.index', roles: ['admin', 'cotizador'] },
    ],
  },
  {
    title: 'Convenios',
    roles: ['admin', 'cotizador'],
    items: [
      { label: 'Clientes', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z', route: 'panel.clientes.index', roles: ['admin', 'cotizador'] },
      { label: 'Tarifas Propias', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', route: 'panel.tarifas-propias.index', roles: ['admin', 'cotizador'] },
      { label: 'Tipos de Servicio', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route: 'panel.tipos-servicio.index', roles: ['admin'] },
      { label: 'Oficinas', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', route: 'panel.oficinas.index', roles: ['admin'] },
    ],
  },
  {
    title: 'Aseguradoras',
    roles: ['admin', 'cotizador'],
    items: [
      { label: 'Aseguradoras', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2M10 8h4m-4 4h4', route: 'panel.aseguradoras.index', roles: ['admin', 'cotizador'] },
      { label: 'Convenios', icon: 'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2', route: 'panel.convenios.index', roles: ['admin', 'cotizador'] },
      { label: 'Tarifas de Convenio', icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', route: 'panel.convenio-tarifas.index', roles: ['admin', 'cotizador'] },
      { label: 'Servicios Especiales', icon: 'M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z', route: 'panel.servicios-especiales.index', roles: ['admin', 'cotizador'] },
      { label: 'Penalizaciones', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z', route: 'panel.penalizaciones.index', roles: ['admin', 'cotizador'] },
      { label: 'Conceptos Adicionales', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', route: 'panel.conceptos-adicionales.index', roles: ['admin', 'cotizador'] },
    ],
  },
  {
    title: 'Flota',
    roles: ['admin'],
    items: [
      { label: 'Unidades', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', route: 'panel.unidades.index', roles: ['admin'] },
      { label: 'Mantenimientos', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', route: 'panel.mantenimientos.index', roles: ['admin'] },
    ],
  },
  {
    title: 'Recursos Humanos',
    roles: ['admin'],
    items: [
      { label: 'Empleados', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', route: 'panel.empleados.index', roles: ['admin'] },
      { label: 'Operadores', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', route: 'panel.operadores.index', roles: ['admin'] },
      { label: 'Usuarios y Accesos', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', route: 'panel.usuarios.index', roles: ['admin'] },
    ],
  },
  {
    title: 'Administración',
    roles: ['admin'],
    items: [
      { label: 'Configuración', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', route: 'panel.configuracion.index', roles: ['admin'] },
      { label: 'Integraciones', icon: 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z', route: 'panel.integraciones.index', roles: ['admin'] },
      { label: 'Reportes', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', route: 'panel.reportes.index', roles: ['admin', 'cotizador'] },
    ],
  },
  {
    title: 'Notificaciones',
    roles: ['admin', 'cotizador', 'operador', 'cliente'],
    items: [
      { label: 'Notificaciones', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', route: 'panel.notificaciones.index', roles: ['admin', 'cotizador', 'operador', 'cliente'] },
    ],
  },
  {
    title: 'Mi Cuenta',
    roles: ['admin', 'cotizador', 'operador', 'cliente'],
    items: [
      { label: 'Mi Perfil', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', route: 'panel.mi-perfil', roles: ['admin', 'cotizador', 'operador', 'cliente'] },
    ],
  },
])

const visibleGroups = computed(() => {
  return menuGroups.value.filter(g => {
    if (g.roles && !g.roles.includes(user.value?.rol)) return false
    return g.items.some(canShow)
  })
})
</script>

<!-- Barra de navegación superior con menú mega, menú de usuario y responsive -->
<template>
  <!-- Barra de navegación fija superior -->
  <header class="fixed top-0 right-0 left-0 z-30 bg-[var(--color-bg)]">
    <div class="flex items-center justify-between px-4 sm:px-6 h-16">
      <!-- Logo y botón de menú móvil -->
      <div class="flex items-center gap-3">
        <button @click="menuOpen = !menuOpen" class="lg:hidden neumorphic-raised-sm p-2 rounded-xl">
          <svg class="w-5 h-5 text-[var(--color-text)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <Link :href="route('panel.dashboard')" class="flex items-center gap-2">
          <div v-if="empresa?.logo" class="w-8 h-8 rounded-lg overflow-hidden">
            <img :src="'/storage/' + empresa.logo" class="w-full h-full object-contain" alt="Logo" />
          </div>
          <div v-else class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: 'var(--color-primary)' }">
            {{ empresa?.siglas?.charAt(0) || 'S' }}
          </div>
          <span class="hidden sm:block text-sm font-semibold text-[var(--color-text)]">{{ empresa?.siglas || 'SIGESGA' }}</span>
        </Link>
      </div>

      <!-- Navegación de escritorio con menú mega -->
      <nav class="hidden lg:flex items-center h-full gap-0.5">
        <div v-for="menuGroup in visibleGroups" :key="menuGroup.title" class="relative h-full flex items-center">
          <div class="relative h-full flex items-center"
               @mouseenter="onMegaEnter(menuGroup.title)"
               @mouseleave="onMegaLeave()">
            <template v-if="getVisibleItems(menuGroup).length === 1">
              <Link :href="route(getVisibleItems(menuGroup)[0].route)"
                    class="px-3 xl:px-4 h-full flex items-center text-sm font-medium relative transition-colors duration-200"
                    :class="isGroupActive(menuGroup) ? 'text-[var(--color-primary)]' : 'text-[var(--color-text)] opacity-75 hover:opacity-100'">
                {{ menuGroup.title }}
                <span class="absolute bottom-0 left-3 xl:left-4 right-3 xl:right-4 h-0.5 rounded-full bg-[var(--color-secondary)] transition-transform duration-300 origin-left"
                      :class="isGroupActive(menuGroup) ? 'scale-x-100' : 'scale-x-0'"></span>
              </Link>
            </template>
            <template v-else>
              <button class="px-3 xl:px-4 h-full flex items-center text-sm font-medium relative transition-colors duration-200 gap-1 cursor-pointer"
                      :class="isGroupActive(menuGroup) || activeMega === menuGroup.title ? 'text-[var(--color-primary)]' : 'text-[var(--color-text)] opacity-75 hover:opacity-100'">
                {{ menuGroup.title }}
                <svg class="w-3 h-3 transition-transform duration-200" :class="activeMega === menuGroup.title ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <span class="absolute bottom-0 left-3 xl:left-4 right-3 xl:right-4 h-0.5 rounded-full bg-[var(--color-secondary)] transition-transform duration-300 origin-left"
                      :class="isGroupActive(menuGroup) || activeMega === menuGroup.title ? 'scale-x-100' : 'scale-x-0'"></span>
              </button>
              <Transition name="mega">
                <div v-if="activeMega === menuGroup.title"
                     class="absolute top-full left-0 pt-2"
                     :class="getVisibleItems(menuGroup).length > 4 ? 'w-[520px]' : 'w-[280px]'">
                  <div class="rounded-2xl bg-[var(--color-surface)] p-3 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] border border-gray-200/10"
                       :class="getVisibleItems(menuGroup).length > 4 ? 'grid grid-cols-2 gap-1' : 'space-y-1'">
                    <Link v-for="item in getVisibleItems(menuGroup)"
                          :key="item.label"
                          :href="route(item.route)"
                          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200"
                          :class="isItemActive(item)
                            ? 'neumorphic-pressed-sm text-[var(--color-secondary)]'
                            : 'text-[var(--color-text)] hover:bg-[var(--color-bg)] hover:translate-x-0.5'">
                      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                      </svg>
                      <span>{{ item.label }}</span>
                    </Link>
                  </div>
                </div>
              </Transition>
            </template>
          </div>
        </div>
      </nav>

      <!-- Menú de usuario y botón de cierre de sesión -->
      <div class="flex items-center gap-2 sm:gap-4">
        <!-- Menú desplegable de usuario -->
        <div class="relative">
          <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-3 neumorphic-raised-sm rounded-2xl px-4 py-2 cursor-pointer">
            <div v-if="user?.foto" class="w-8 h-8 rounded-full overflow-hidden">
              <img :src="'/storage/' + user.foto" class="w-full h-full object-cover" alt="Foto" />
            </div>
            <div v-else class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold" :style="{ backgroundColor: 'var(--color-primary)' }">
              {{ user?.name?.charAt(0) || 'U' }}
            </div>
            <div class="hidden sm:block text-left">
              <p class="text-sm font-medium text-[var(--color-text)]">{{ user?.name || 'Usuario' }}</p>
              <p class="text-xs text-[var(--color-text)] opacity-60 capitalize">{{ user?.rol || '' }}</p>
            </div>
          </button>
          <div v-if="userMenuOpen" class="absolute right-0 mt-2 w-48 rounded-2xl bg-[var(--color-surface)] p-2 shadow-[8px_8px_16px_var(--neumorphic-dark),-8px_-8px_16px_var(--neumorphic-light)] z-50">
            <Link :href="route('panel.mi-perfil')" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm text-[var(--color-text)] hover:bg-[var(--color-bg)] transition-all">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
              Mi Perfil
            </Link>
            <button @click="logout" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-all w-full text-left">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
              Cerrar Sesión
            </button>
          </div>
        </div>
        <button @click="logout" class="neumorphic-raised-sm p-2 rounded-xl" title="Cerrar sesión">
          <svg class="w-5 h-5 text-[var(--color-text)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Menú responsive para móvil -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-if="menuOpen" class="lg:hidden border-t border-gray-200/20">
        <div class="max-h-[calc(100vh-4rem)] overflow-y-auto bg-[var(--color-surface)] shadow-[0_8px_16px_var(--neumorphic-dark)]">
          <div class="px-4 py-4 space-y-4">
            <div v-for="group in visibleGroups" :key="group.title">
              <p class="text-xs font-semibold uppercase tracking-wider px-3 mb-1.5" :style="{ color: 'var(--color-secondary)' }">{{ group.title }}</p>
              <div class="space-y-0.5">
                <Link
                  v-for="item in getVisibleItems(group)"
                  :key="item.label"
                  :href="route(item.route)"
                  @click="menuOpen = false"
                  class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200"
                  :class="isItemActive(item)
                    ? 'neumorphic-pressed-sm text-[var(--color-secondary)]'
                    : 'text-[var(--color-text)] opacity-70 hover:neumorphic-raised-sm hover:opacity-100'"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                  </svg>
                  <span>{{ item.label }}</span>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </header>
</template>

<style scoped>
.mega-enter-active {
  transition: all 0.2s ease-out;
}
.mega-enter-from {
  opacity: 0;
  transform: translateY(-6px);
}
.mega-leave-active {
  transition: all 0.15s ease-in;
}
.mega-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>

<script setup>
import { ref, computed, inject } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const empresa = computed(() => page.props.empresa)
const sidebarOpen = inject('sidebarOpen', ref(false))

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
    title: 'Catálogos',
    roles: ['admin', 'cotizador'],
    items: [
      { label: 'Clientes', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z', route: 'panel.clientes.index', roles: ['admin', 'cotizador'] },
      { label: 'Aseguradoras', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2M10 8h4m-4 4h4', route: 'panel.aseguradoras.index', roles: ['admin', 'cotizador'] },
      { label: 'Tipos de Servicio', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route: 'panel.tipos-servicio.index', roles: ['admin'] },
      { label: 'Convenios', icon: 'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2', route: 'panel.convenios.index', roles: ['admin', 'cotizador'] },
      { label: 'Tarifas Propias', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', route: 'panel.tarifas-propias.index', roles: ['admin', 'cotizador'] },
      { label: 'Oficinas', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', route: 'panel.oficinas.index', roles: ['admin'] },
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
      { label: 'Notificaciones', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', route: 'panel.notificaciones.index', roles: ['admin'] },
      { label: 'Reportes', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', route: 'panel.reportes.index', roles: ['admin', 'cotizador'] },
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

function canShow(group) {
  if (!group.roles) return true
  if (!user.value) return false
  return group.roles.includes(user.value.rol)
}

function canShowItem(item) {
  if (!item.roles) return true
  if (!user.value) return false
  return item.roles.includes(user.value.rol)
}
</script>

<template>
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-40 w-64 bg-[var(--color-bg)] transform transition-transform duration-300 lg:translate-x-0',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full',
    ]"
  >
    <div class="flex flex-col h-full p-4">
      <div class="flex items-center gap-4 px-4 py-6 mb-6 neumorphic-raised rounded-2xl">
        <div v-if="empresa?.logo" class="w-20 h-20 rounded-2xl overflow-hidden flex-shrink-0 shadow-[inset_2px_2px_4px_var(--neumorphic-dark),inset_-2px_-2px_4px_var(--neumorphic-light)]">
          <img :src="'/storage/' + empresa.logo" class="w-full h-full object-contain" alt="Logo" />
        </div>
        <div v-else class="w-20 h-20 rounded-2xl flex items-center justify-center text-white font-bold text-2xl flex-shrink-0" :style="{ backgroundColor: 'var(--color-primary)' }">
          {{ empresa?.siglas?.charAt(0) || 'S' }}
        </div>
        <div class="min-w-0 flex-1">
          <h2 class="font-semibold text-[var(--color-text)] text-sm leading-tight">{{ empresa?.siglas || 'SIGESGA' }}</h2>
          <p class="text-[var(--color-text)] opacity-60 text-[11px] leading-tight mt-0.5 break-words">{{ empresa?.nombre || 'Sistema de Gestión' }}</p>
        </div>
      </div>

      <nav class="flex-1 overflow-y-auto space-y-6">
        <div v-for="group in menuGroups" :key="group.title">
          <div v-if="canShow(group)">
            <p class="text-xs font-semibold uppercase tracking-wider px-4 mb-2" :style="{ color: 'var(--color-secondary)' }">{{ group.title }}</p>
            <div class="space-y-1">
              <template v-for="item in group.items" :key="item.label">
                <Link
                  v-if="canShowItem(item)"
                  :href="route(item.route)"
                :class="[
                  'flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
                  route().current(item.route)
                    ? 'neumorphic-pressed text-[var(--color-secondary)]'
                    : 'text-[var(--color-text)] opacity-70 hover:neumorphic-raised hover:text-[var(--color-secondary)] hover:opacity-100'
                ]"
              >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                </svg>
                <span>{{ item.label }}</span>
              </Link>
              </template>
            </div>
          </div>
        </div>
      </nav>

      <div class="mt-auto pt-4">
        <div class="neumorphic-raised rounded-2xl p-3">
          <p class="text-[var(--color-text)] opacity-60 text-xs text-center">{{ empresa?.texto_derechos || '© 2026 SIGESGA' }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.neumorphic-raised {
  box-shadow: 8px 8px 16px var(--neumorphic-dark, #d0d5da), -8px -8px 16px var(--neumorphic-light, #ffffff);
}
.neumorphic-pressed {
  box-shadow: inset 4px 4px 8px var(--neumorphic-dark, #d0d5da), inset -4px -4px 8px var(--neumorphic-light, #ffffff);
}
</style>

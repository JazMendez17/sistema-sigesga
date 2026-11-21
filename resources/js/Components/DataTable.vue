<script setup>
import { ref, computed, watch } from 'vue'
import SkeletonLoader from '@/Components/SkeletonLoader.vue'

const props = defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, default: () => [] },
  pagination: { type: Object, default: null },
  pageSize: { type: Number, default: 15 },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['sort', 'pageChange', 'rowClick'])

const sortField = ref(null)
const sortDir = ref('asc')
const currentPage = ref(1)

watch(() => props.data, () => {
  currentPage.value = 1
})

function toggleSort(key) {
  if (sortField.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = key
    sortDir.value = 'asc'
  }
  emit('sort', { field: sortField.value, dir: sortDir.value })
}

const sortedData = computed(() => {
  const list = Array.isArray(props.data) ? [...props.data] : []
  if (!sortField.value) return list
  const key = sortField.value
  const dir = sortDir.value === 'asc' ? 1 : -1
  return list.sort((a, b) => {
    const va = a?.[key]
    const vb = b?.[key]
    if (va == null && vb == null) return 0
    if (va == null) return 1
    if (vb == null) return -1
    if (typeof va === 'number' && typeof vb === 'number') return (va - vb) * dir
    return String(va).localeCompare(String(vb), 'es', { numeric: true, sensitivity: 'base' }) * dir
  })
})

const totalClientPages = computed(() => {
  if (props.pagination || !props.pageSize || props.pageSize <= 0) return 1
  return Math.max(1, Math.ceil(sortedData.value.length / props.pageSize))
})

const visibleData = computed(() => {
  if (props.pagination || !props.pageSize || props.pageSize <= 0) return sortedData.value
  const start = (currentPage.value - 1) * props.pageSize
  return sortedData.value.slice(start, start + props.pageSize)
})

const clientRange = computed(() => {
  const total = sortedData.value.length
  if (total === 0) return { from: 0, to: 0, total: 0 }
  const from = (currentPage.value - 1) * props.pageSize + 1
  const to = Math.min(total, currentPage.value * props.pageSize)
  return { from, to, total }
})

function sortIcon(key) {
  if (sortField.value !== key) return 'M7 10l5 5 5-5'
  return sortDir.value === 'asc' ? 'M7 14l5-5 5 5' : 'M7 10l5 5 5-5'
}
</script>

<!-- Tabla de datos reutilizable con ordenamiento, paginación y carga con esqueleto -->
<template>
  <div class="neumorphic-raised rounded-3xl bg-[var(--color-surface)] overflow-hidden">
    <div class="overflow-x-auto">
      <!-- Tabla de datos -->
      <table class="w-full">
        <!-- Cabecera de la tabla -->
        <thead>
          <tr class="border-b border-[var(--neumorphic-dark)]/30">
            <th
              v-for="col in columns"
              :key="col.key"
              @click="col.sortable && toggleSort(col.key)"
              :class="['px-4 py-3 text-left text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider', col.sortable ? 'cursor-pointer hover:text-[var(--color-text)]' : '']"
            >
              <div class="flex items-center gap-1">
                <span>{{ col.label }}</span>
                <svg v-if="col.sortable" class="w-3 h-3" :class="sortField === col.key ? 'text-[var(--color-secondary)]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sortIcon(col.key)" />
                </svg>
              </div>
            </th>
            <th v-if="$slots.actions || $slots['cell-acciones']" class="px-4 py-3 text-right text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <!-- Cuerpo de la tabla -->
        <tbody class="divide-y divide-[var(--neumorphic-dark)]/20">
          <!-- Esqueleto de carga -->
          <template v-if="loading">
            <tr v-for="n in 5" :key="'sk-' + n">
              <td :colspan="columns.length + 1" class="px-4 py-2">
                <SkeletonLoader type="table-row" :height="'2.5rem'" />
              </td>
            </tr>
          </template>
          <!-- Filas de datos -->
          <template v-else>
            <tr v-for="(row, i) in visibleData" :key="row?.id ?? i" @click="$emit('rowClick', row)" class="cursor-pointer hover:bg-white/30 transition-colors">
              <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-sm text-[var(--color-text)] whitespace-nowrap">
                <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                  {{ row[col.key] }}
                </slot>
              </td>
              <td v-if="$slots.actions || $slots['cell-acciones']" class="px-4 py-3 text-right whitespace-nowrap">
                <slot name="actions" :row="row" />
                <slot name="cell-acciones" :row="row" />
              </td>
            </tr>
            <tr v-if="visibleData.length === 0">
              <td :colspan="columns.length + ($slots.actions || $slots['cell-acciones'] ? 1 : 0)" class="px-4 py-12 text-center text-[var(--color-text-muted)]">
                No hay datos disponibles
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Paginación servidor -->
    <div v-if="pagination" class="flex items-center justify-between px-4 py-3 border-t border-[var(--neumorphic-dark)]/30">
      <p class="text-sm text-[var(--color-text-muted)]">
        Mostrando {{ pagination.from || 0 }} - {{ pagination.to || 0 }} de {{ pagination.total || 0 }}
      </p>
      <div class="flex gap-2">
        <button type="button" :disabled="!pagination.prev" @click="$emit('pageChange', pagination.prev)" class="neumorphic-pressed px-3 py-1.5 rounded-xl text-sm disabled:opacity-30">
          Anterior
        </button>
        <button type="button" :disabled="!pagination.next" @click="$emit('pageChange', pagination.next)" class="neumorphic-pressed px-3 py-1.5 rounded-xl text-sm disabled:opacity-30">
          Siguiente
        </button>
      </div>
    </div>

    <!-- Paginación cliente -->
    <div v-else-if="totalClientPages > 1" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-t border-[var(--neumorphic-dark)]/30">
      <p class="text-sm text-[var(--color-text-muted)]">
        Mostrando {{ clientRange.from }} - {{ clientRange.to }} de {{ clientRange.total }}
      </p>
      <div class="flex items-center gap-2">
        <button type="button" :disabled="currentPage <= 1" @click="currentPage--" class="neumorphic-pressed px-3 py-1.5 rounded-xl text-sm disabled:opacity-30">
          Anterior
        </button>
        <span class="text-xs font-medium text-[var(--color-text-muted)]">Página {{ currentPage }} de {{ totalClientPages }}</span>
        <button type="button" :disabled="currentPage >= totalClientPages" @click="currentPage++" class="neumorphic-pressed px-3 py-1.5 rounded-xl text-sm disabled:opacity-30">
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.neumorphic-raised {
  box-shadow: 8px 8px 16px var(--neumorphic-dark), -8px -8px 16px var(--neumorphic-light);
}
.neumorphic-pressed {
  box-shadow: inset 3px 3px 6px var(--neumorphic-dark), inset -3px -3px 6px var(--neumorphic-light);
}
</style>

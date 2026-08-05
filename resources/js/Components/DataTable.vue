<script setup>
import { ref, computed } from 'vue'
import SkeletonLoader from '@/Components/SkeletonLoader.vue'

const props = defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, default: () => [] },
  pagination: { type: Object, default: null },
  loading: { type: Boolean, default: false },
})

defineEmits(['sort', 'pageChange', 'rowClick'])

const sortField = ref(null)
const sortDir = ref('asc')

function toggleSort(key) {
  if (sortField.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = key
    sortDir.value = 'asc'
  }
}

function sortIcon(key) {
  if (sortField.value !== key) return 'M7 10l5 5 5-5'
  return sortDir.value === 'asc' ? 'M7 14l5-5 5 5' : 'M7 10l5 5 5-5'
}
</script>

<template>
  <div class="neumorphic-raised rounded-3xl bg-[var(--color-surface)] overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
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
        <tbody class="divide-y divide-[var(--neumorphic-dark)]/20">
          <template v-if="loading">
            <tr v-for="n in 5" :key="'sk-' + n">
              <td :colspan="columns.length + 1" class="px-4 py-2">
                <SkeletonLoader type="table-row" :height="'2.5rem'" />
              </td>
            </tr>
          </template>
          <template v-else>
            <tr v-for="(row, i) in data" :key="i" @click="$emit('rowClick', row)" class="cursor-pointer hover:bg-white/30 transition-colors">
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
            <tr v-if="data.length === 0">
              <td :colspan="columns.length + ($slots.actions || $slots['cell-acciones'] ? 1 : 0)" class="px-4 py-12 text-center text-[var(--color-text-muted)]">
                No hay datos disponibles
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div v-if="pagination" class="flex items-center justify-between px-4 py-3 border-t border-[var(--neumorphic-dark)]/30">
      <p class="text-sm text-[var(--color-text-muted)]">
        Mostrando {{ pagination.from || 0 }} - {{ pagination.to || 0 }} de {{ pagination.total || 0 }}
      </p>
      <div class="flex gap-2">
        <button :disabled="!pagination.prev" class="neumorphic-pressed px-3 py-1.5 rounded-xl text-sm disabled:opacity-30">
          Anterior
        </button>
        <button :disabled="!pagination.next" class="neumorphic-pressed px-3 py-1.5 rounded-xl text-sm disabled:opacity-30">
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

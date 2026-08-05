<!-- Botón primario con color de tema -->
<template>
    <!-- Botón de acción principal -->
    <button
        class="inline-flex items-center rounded-md border border-transparent px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:opacity-90 focus:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-offset-2 active:opacity-80"
        :style="{ backgroundColor: 'var(--color-primary)' }"
    >
        <slot />
    </button>
</template>

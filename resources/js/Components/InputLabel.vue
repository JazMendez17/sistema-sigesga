<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<!-- Etiqueta para campos de formulario -->
<template>
    <!-- Etiqueta con soporte para slot o prop value -->
    <label class="block text-sm font-medium text-gray-700">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>

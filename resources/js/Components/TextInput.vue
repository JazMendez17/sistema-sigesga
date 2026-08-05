<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<!-- Campo de entrada de texto con v-model y soporte para autofocus -->
<template>
    <!-- Input de texto genérico -->
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"
        v-model="model"
        ref="input"
    />
</template>

import { ref } from 'vue'

export const validationErrors = ref([])

export function showValidationErrors(errors) {
  validationErrors.value = errors
}

export function clearValidationErrors() {
  validationErrors.value = []
}

import { ref, computed } from 'vue'
import { showValidationErrors } from '@/stores/notification'

export function useFormValidation(form, rules) {
  const clientErrors = ref({})
  const submitted = ref(false)

  const mergeErrors = computed(() => {
    const all = { ...form.errors }
    for (const key of Object.keys(clientErrors.value)) {
      if (clientErrors.value[key]) {
        all[key] = clientErrors.value[key]
      }
    }
    return all
  })

  function getError(field) {
    return mergeErrors.value[field] || ''
  }

  function clearFieldError(field) {
    delete clientErrors.value[field]
  }

  function trimAll() {
    const data = form.data()
    for (const key of Object.keys(data)) {
      if (typeof data[key] === 'string') {
        form[key] = data[key].trim()
      }
    }
    if (form.direccion) {
      for (const key of Object.keys(form.direccion)) {
        if (typeof form.direccion[key] === 'string') {
          form.direccion[key] = form.direccion[key].trim()
        }
      }
    }
  }

  function validate() {
    submitted.value = true
    const errors = {}
    const data = form.data()

    for (const [field, fieldRules] of Object.entries(rules)) {
      const value = getFieldValue(data, field)
      const messages = []
      for (const rule of fieldRules) {
        const msg = applyRule(field, value, rule, data)
        if (msg) messages.push(msg)
      }
      if (messages.length > 0) {
        errors[field] = messages[0]
      }
    }
    clientErrors.value = errors
    return Object.keys(errors).length === 0
  }

  function getFieldValue(data, path) {
    return path.split('.').reduce((obj, key) => obj?.[key], data)
  }

  function applyRule(field, value, rule, data) {
    if (typeof rule === 'string') {
      const parts = rule.split(':')
      const name = parts[0]
      const param = parts[1]
      return validateRule(name, value, param, data, field)
    }
    if (typeof rule === 'object' && rule.rule) {
      return validateRule(rule.rule, value, rule.param, data, field, rule.message)
    }
    return ''
  }

  function validateRule(name, value, param, data, field, customMessage) {
    if (value === undefined || value === null) value = ''

    switch (name) {
      case 'required': {
        if (value === '' || value === null || value === undefined) {
          return customMessage || 'Este campo es obligatorio'
        }
        return ''
      }
      case 'min': {
        const min = parseInt(param)
        if (value !== '' && typeof value === 'string' && value.length < min) {
          return customMessage || `Mínimo ${min} caracteres`
        }
        if (value !== '' && typeof value === 'number' && value < min) {
          return customMessage || `El valor mínimo es ${min}`
        }
        return ''
      }
      case 'max': {
        const max = parseInt(param)
        if (value !== '' && typeof value === 'string' && value.length > max) {
          return customMessage || `Máximo ${max} caracteres`
        }
        if (value !== '' && typeof value === 'number' && value > max) {
          return customMessage || `El valor máximo es ${max}`
        }
        return ''
      }
      case 'email': {
        if (value !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          return customMessage || 'Ingresa un correo electrónico válido'
        }
        return ''
      }
      case 'numeric': {
        if (value !== '' && isNaN(parseFloat(value))) {
          return customMessage || 'Ingresa un valor numérico válido'
        }
        return ''
      }
      case 'integer': {
        if (value !== '' && (!/^\d+$/.test(value))) {
          return customMessage || 'Ingresa un número entero válido'
        }
        return ''
      }
      case 'min_value': {
        const minVal = parseFloat(param)
        if (value !== '' && parseFloat(value) < minVal) {
          return customMessage || `El valor mínimo es ${minVal}`
        }
        return ''
      }
      case 'max_value': {
        const maxVal = parseFloat(param)
        if (value !== '' && parseFloat(value) > maxVal) {
          return customMessage || `El valor máximo es ${maxVal}`
        }
        return ''
      }
      case 'pattern': {
        if (value !== '' && !new RegExp(param).test(value)) {
          return customMessage || 'Formato inválido'
        }
        return ''
      }
      case 'curp': {
        if (value !== '' && !/^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9][0-9A-Z]$/.test(value.toUpperCase())) {
          return customMessage || 'CURP inválida (debe tener 18 caracteres alfanuméricos)'
        }
        return ''
      }
      case 'rfc': {
        if (value !== '' && !/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/.test(value.toUpperCase())) {
          return customMessage || 'RFC inválido'
        }
        return ''
      }
      case 'phone': {
        if (value !== '' && !/^[\d\s\-()+]{7,20}$/.test(value)) {
          return customMessage || 'Teléfono inválido'
        }
        return ''
      }
      case 'placas': {
        if (value !== '' && !/^[A-Z0-9]{3,8}$/.test(value.toUpperCase())) {
          return customMessage || 'Formato de placas inválido'
        }
        return ''
      }
      case 'date': {
        if (value !== '' && isNaN(Date.parse(value))) {
          return customMessage || 'Fecha inválida'
        }
        return ''
      }
      case 'after_or_equal': {
        if (value && data[param]) {
          const refDate = new Date(data[param])
          const curDate = new Date(value)
          if (curDate < refDate) {
            return customMessage || `Debe ser igual o posterior a la fecha de ${param.replace(/_/g, ' ')}`
          }
        }
        return ''
      }
      case 'boolean': {
        if (value !== '' && value !== true && value !== false && value !== 0 && value !== 1 && value !== '0' && value !== '1') {
          return customMessage || 'Valor inválido'
        }
        return ''
      }
      case 'url': {
        if (value !== '' && !/^https?:\/\/.+/.test(value)) {
          return customMessage || 'Ingresa una URL válida (https://...)'
        }
        return ''
      }
      default:
        return ''
    }
  }

  function handleInput(field) {
    clearFieldError(field)
    form.clearErrors(field)
  }

  function handleSubmit(callback) {
    return function () {
      trimAll()
      if (!validate()) {
        const all = Object.values(clientErrors.value).filter(Boolean)
        if (all.length > 0) showValidationErrors(all)
        return
      }
      callback()
    }
  }

  return {
    clientErrors,
    submitted,
    mergeErrors,
    getError,
    validate,
    handleInput,
    handleSubmit,
    trimAll,
    clearFieldError,
  }
}

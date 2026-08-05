const SPECIAL_CHARS = '!@#$%^&*()_+-=[]{}|;:,.<>?~'

export const CARACTERES_ESPECIALES = SPECIAL_CHARS

export const REQUISITOS = {
    length: { label: 'Mínimo 8 caracteres', test: (pwd) => pwd.length >= 8 },
    uppercase: { label: 'Al menos una mayúscula (A-Z)', test: (pwd) => /[A-Z]/.test(pwd) },
    lowercase: { label: 'Al menos una minúscula (a-z)', test: (pwd) => /[a-z]/.test(pwd) },
    number: { label: 'Al menos un número (0-9)', test: (pwd) => /\d/.test(pwd) },
    special: { label: `Al menos un carácter especial (${SPECIAL_CHARS})`, test: (pwd) => /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?~]/.test(pwd) },
}

import { computed } from 'vue'

export function usePasswordStrength(password) {
    const resultados = computed(() => {
        const pwd = password.value || ''
        const checks = {}
        for (const [key, req] of Object.entries(REQUISITOS)) {
            checks[key] = { met: req.test(pwd), label: req.label }
        }
        return checks
    })

    const metCount = computed(() => {
        return Object.values(resultados.value).filter(r => r.met).length
    })

    const level = computed(() => {
        const count = metCount.value
        if (count <= 1) return { level: 0, label: 'Baja', color: '#EF4444', width: '20%' }
        if (count <= 3) return { level: 1, label: 'Media', color: '#F59E0B', width: '55%' }
        if (count <= 4) return { level: 2, label: 'Alta', color: '#10B981', width: '80%' }
        return { level: 3, label: 'Segura', color: '#059669', width: '100%' }
    })

    const isValid = computed(() => metCount.value >= 4)

    const errores = computed(() => {
        if (!password.value) return []
        const failed = []
        for (const [key, req] of Object.entries(REQUISITOS)) {
            if (!req.test(password.value)) {
                failed.push(req.label)
            }
        }
        return failed
    })

    return { resultados, metCount, level, isValid, errores, CARACTERES_ESPECIALES }
}

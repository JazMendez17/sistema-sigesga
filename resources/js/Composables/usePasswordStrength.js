// Composable para evaluar la fortaleza de una contraseña

const SPECIAL_CHARS = '!@#$%^&*()_+-=[]{}|;:,.<>?~'

export const CARACTERES_ESPECIALES = SPECIAL_CHARS

// Requisitos mínimos de seguridad para contraseñas
export const REQUISITOS = {
    length: { label: 'Mínimo 8 caracteres', test: (pwd) => pwd.length >= 8 },
    uppercase: { label: 'Al menos una mayúscula (A-Z)', test: (pwd) => /[A-Z]/.test(pwd) },
    lowercase: { label: 'Al menos una minúscula (a-z)', test: (pwd) => /[a-z]/.test(pwd) },
    number: { label: 'Al menos un número (0-9)', test: (pwd) => /\d/.test(pwd) },
    special: { label: `Al menos un carácter especial (${SPECIAL_CHARS})`, test: (pwd) => /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?~]/.test(pwd) },
}

import { computed } from 'vue'

// Evalúa la fortaleza de la contraseña y retorna métricas y resultados
export function usePasswordStrength(password) {
    // Revisa cada requisito contra la contraseña actual
    const resultados = computed(() => {
        const pwd = password.value || ''
        const checks = {}
        for (const [key, req] of Object.entries(REQUISITOS)) {
            checks[key] = { met: req.test(pwd), label: req.label }
        }
        return checks
    })

    // Cuenta cuántos requisitos se cumplen
    const metCount = computed(() => {
        return Object.values(resultados.value).filter(r => r.met).length
    })

    // Determina el nivel de fortaleza (Baja, Media, Alta, Segura)
    const level = computed(() => {
        const count = metCount.value
        if (count <= 1) return { level: 0, label: 'Baja', color: '#EF4444', width: '20%' }
        if (count <= 3) return { level: 1, label: 'Media', color: '#F59E0B', width: '55%' }
        if (count <= 4) return { level: 2, label: 'Alta', color: '#10B981', width: '80%' }
        return { level: 3, label: 'Segura', color: '#059669', width: '100%' }
    })

    // Indica si la contraseña cumple al menos 4 requisitos
    const isValid = computed(() => metCount.value >= 4)

    // Lista de requisitos que no se cumplen
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

const ETIQUETAS = {
  admin: 'Administrador',
  cotizador: 'Cotizador',
  operador: 'Operador',
  cliente: 'Cliente',
  pendiente: 'Pendiente',
  aprobado: 'Aprobado',
  rechazada: 'Rechazada',
  rechazado: 'Rechazado',
  aprobada: 'Aprobada',
  asignado: 'Asignado',
  inicio_servicio: 'En camino',
  en_sitio_origen: 'En origen',
  salida_destino: 'En tránsito',
  en_destino: 'En destino',
  finalizado: 'Finalizado',
  cancelado: 'Cancelado',
  solicitud_cancelacion: 'Cancelación solicitada',
  cancelado_por_cotizador: 'Cancelado por cotizador',
  cancelado_por_admin: 'Cancelado por administrador',
  en_negociacion: 'En negociación',
  vigente: 'Vigente',
  vencido: 'Vencido',
  local: 'Local',
  foraneo: 'Foráneo',
  whatsapp: 'WhatsApp',
  sistema_push: 'Sistema',
  sms: 'SMS',
  email: 'Correo electrónico',
}

export function etiqueta(valor) {
  if (valor === null || valor === undefined || valor === '') return '—'
  const clave = String(valor).trim()
  return ETIQUETAS[clave] || clave.replaceAll('_', ' ').replace(/\b\w/g, letra => letra.toUpperCase())
}

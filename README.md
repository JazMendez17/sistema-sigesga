<div align="center">
  <h1>🚛 SIGESGA</h1>
  <p><strong>Sistema de Gestión de Grúas y Asistencias Viales</strong></p>
  <p>Aplicación web para la administración integral de servicios de grúas y asistencia en carretera.</p>
  <p>
    <img src="https://img.shields.io/badge/Laravel-12-red?logo=laravel" alt="Laravel 12">
    <img src="https://img.shields.io/badge/Inertia.js-blue?logo=inertia" alt="Inertia.js">
    <img src="https://img.shields.io/badge/Vue_3-green?logo=vue.js" alt="Vue 3">
    <img src="https://img.shields.io/badge/PHP-8.2-purple?logo=php" alt="PHP 8.2">
    <img src="https://img.shields.io/badge/license-MIT-brightgreen" alt="MIT License">
  </p>
</div>

---

## 📋 Descripción

SIGESGA es un sistema integral desarrollado con **Laravel 12**, **Inertia.js** y **Vue 3** que permite gestionar de principio a fin los servicios de una empresa de grúas y asistencia vial. Cuenta con un panel administrativo con control de acceso basado en roles (admin, cotizador, operador) y una landing page pública para que los clientes puedan solicitar servicios, rastrear unidades y contactar a soporte.

---

## ⚙️ Funcionalidades

### 🌐 Landing Page
- Solicitud de servicios de grúa en línea
- Rastreo de servicios en tiempo real
- Formulario de contacto y soporte

### 👥 Panel de Administración
- **Dashboard** con indicadores y resúmenes
- **Cotizaciones** — Creación y gestión de presupuestos
- **Servicios** — Seguimiento y control de servicios
- **Facturación** — Generación y administración de facturas
- **Clientes** — Registro y gestión de clientes
- **Aseguradoras** — Administración de compañías aseguradoras
- **Convenios** — Acuerdos con aseguradoras y empresas
- **Tarifas** — Configuración de tarifas propias y por convenio
- **Unidades** — Control de flotilla de grúas
- **Mantenimientos** — Registro de mantenimiento de unidades
- **Empleados** — Gestión del personal operativo y administrativo
- **Operadores** — Asignación y control de operadores
- **Oficinas** — Administración de sucursales
- **Usuarios** — Gestión de accesos al sistema
- **Configuración** — Personalización del sistema
- **Integraciones** — Conexión con servicios externos
- **Notificaciones** — Sistema de notificaciones internas

---

## 🔑 Cuentas de Prueba

Al ejecutar los seeders, se crean automáticamente las siguientes cuentas para probar el sistema:

| Rol | Nombre | Correo Electrónico | Contraseña |
|:---:|--------|-------------------|:----------:|
| Administrador | Juan Carlos Pérez | admin@sigesga.com | `123456` |
| Cotizador | Cotizador Sistema | cotizador@sigesga.com | `123456` |
| Operador | Roberto Méndez | operador@sigesga.com | `123456` |
| Cliente | Cliente Demo | cliente@sigesga.com | `123456` |

> **Nota:** El rol `admin` tiene acceso completo al sistema. Los roles `cotizador`, `operador` y `cliente` tienen permisos limitados según su perfil.

---

## 🚀 Requisitos

- PHP ^8.2
- Composer
- Node.js 18+ y npm
- Base de datos (MySQL, MariaDB, PostgreSQL, SQLite o TiDB)

---

## 📦 Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/JazMendez17/gruas-sigesga.git

# 2. Acceder al directorio
cd gruas-sigesga

# 3. Instalar dependencias de PHP
composer install

# 4. Instalar dependencias de Node.js
npm install

# 5. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Compilar assets
npm run build

# 8. Iniciar el servidor de desarrollo
composer run dev
```

El servidor estará disponible en `http://localhost:8000`.

---

## 💻 Desarrollo

```bash
# Iniciar servidor con recarga en caliente
composer run dev
```

Este comando ejecuta de forma concurrente:
- `php artisan serve` — Servidor HTTP
- `php artisan queue:listen` — Cola de trabajos
- `php artisan pail` — Logs en tiempo real
- `npm run dev` — Vite con HMR

---

## 🐳 Stack Tecnológico

| Tecnología | Propósito |
|-----------|-----------|
| Laravel 12 | Framework PHP backend |
| Inertia.js | Puente entre backend y frontend |
| Vue 3 | Framework frontend (SPA) |
| Tailwind CSS | Estilos y diseño responsivo |
| MySQL / TiDB | Base de datos relacional |
| Laravel Sanctum | Autenticación de APIs |

---

## 📄 Licencia

Este proyecto es de código abierto bajo la licencia [MIT](LICENSE).

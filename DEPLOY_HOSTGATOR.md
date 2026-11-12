# Publicar SIGESGA en HostGator

Guía para `https://www.sigesga.com`. El archivo `.env` local queda fuera del repositorio; en HostGator se crea uno nuevo usando `deploy/.env.hostgator.example` como plantilla.

## Antes de subir

En cPanel confirma:

- El dominio `www.sigesga.com` apunta a esta cuenta y tiene SSL activo.
- PHP **8.4 o superior** está disponible para el dominio y para la terminal. `composer.json` requiere PHP `^8.4`; no uses `--ignore-platform-reqs` para forzar la instalación.
- Las extensiones PHP están activas: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `dom`, `ctype`, `fileinfo`, `bcmath`, `gd`, `zip` y `curl`.
- Tienes una base MySQL vacía y un usuario con todos los privilegios sobre ella.

El repositorio ya contiene `vendor/` y `public/build/`, así que la publicación no necesita Node.js ni compilar assets en HostGator. Si actualizas dependencias, vuelve a instalar Composer y compilar los assets antes de publicar.

## 1. Subir el repositorio

El repositorio es público. Puedes clonarlo con cPanel **Git Version Control** o desde Terminal:

```bash
cd ~
git clone https://github.com/JazMendez17/sistema-sigesga.git sigesga
```

Deja el proyecto en `/home/USUARIO/sigesga`, fuera de `public_html`. No subas `.env`, respaldos `.sql`, `node_modules` ni claves privadas.

## 2. Configurar el dominio

En cPanel → **Domains**, configura el document root de `www.sigesga.com` como:

```text
/home/USUARIO/sigesga/public
```

Reemplaza `USUARIO` por el usuario de cPanel. Laravel debe publicar únicamente esa carpeta. **No apuntes el dominio a la raíz del repositorio ni copies todo el repositorio dentro de `public_html`.** Si cPanel no permite cambiar el document root del dominio principal, pide a HostGator que lo configure; evita los atajos que exponen archivos internos.

Confirma también que el dominio raíz `sigesga.com` redirija a `https://www.sigesga.com` o apunte al mismo sitio. En cPanel ejecuta AutoSSL para ambos nombres.

## 3. Crear la base de datos

En cPanel → **MySQL Databases**:

1. Crea una base de datos y un usuario. HostGator agregará el prefijo de cPanel.
2. Asigna el usuario a la base con **ALL PRIVILEGES**.
3. Copia los nombres completos con prefijo para configurar `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

En HostGator normalmente `DB_HOST=localhost`. No reutilices aquí las credenciales de la base local.

## 4. Crear el `.env` de producción

En cPanel File Manager, abre `/home/USUARIO/sigesga`, activa la opción para mostrar archivos ocultos y crea `.env`. Copia el contenido de [`deploy/.env.hostgator.example`](deploy/.env.hostgator.example) y completa los valores de tu cuenta. No copies el `.env` de tu computadora.

En particular, reemplaza:

- Los valores `DB_*` por las credenciales de la base de HostGator.
- `MAIL_*` por una cuenta SMTP real. Puedes dejar `MAIL_MAILER=log` al principio y configurar correo después.
- Las llaves de Google Maps y Postalia solo si activarás esas funciones; usa llaves nuevas y restringidas al dominio.

Usa una `APP_KEY` exclusiva para producción. En Terminal puedes generarla con:

```bash
cd ~/sigesga
/opt/cpanel/ea-php84/root/usr/bin/php artisan key:generate --force
```

Si esa ruta no existe, localiza el binario PHP 8.4 disponible en el servidor o selecciona PHP 8.4 en cPanel. No compartas el `.env` ni guardes sus valores en GitHub.

## 5. Preparar Laravel

Si SSH no conecta, habilita **Shell Access** en el portal de HostGator y usa Terminal de cPanel si está disponible. Para hosting compartido la conexión SSH usa el puerto `2222`; utiliza el usuario de cPanel y la IP que muestra el portal.

Con PHP 8.4 seleccionado, ejecuta:

```bash
cd ~/sigesga
PHP=/opt/cpanel/ea-php84/root/usr/bin/php
$PHP -v
$PHP artisan migrate --force
$PHP artisan db:seed --force
$PHP artisan optimize
chmod -R 775 storage bootstrap/cache public/storage
```

Si Composer está disponible y necesitas regenerar dependencias, ejecuta `composer install --no-dev --optimize-autoloader` usando PHP 8.4. El repositorio ya contiene `vendor/`, por lo que no es un paso obligatorio para esta versión.

`db:seed` agrega datos de demostración y cuatro cuentas de prueba con la contraseña inicial publicada `12345678`. Úsalo solo para una instalación de demostración; cambia inmediatamente las contraseñas de todas las cuentas. Para un sitio con datos reales, no ejecutes los seeders de demostración.

## 6. Comprobar el sitio

Abre:

- `https://www.sigesga.com/up` — debe responder correctamente.
- `https://www.sigesga.com/` — debe mostrar la página principal con estilos.
- `https://www.sigesga.com/.env` — debe responder 403 o 404.

Si aparece un error 500, consulta `storage/logs/laravel.log`. Mantén `APP_DEBUG=false` en producción.

## Actualizar después

```bash
cd ~/sigesga
git pull origin main
PHP=/opt/cpanel/ea-php84/root/usr/bin/php
$PHP artisan migrate --force
$PHP artisan optimize
```

No reemplaces `.env` al actualizar. Si cambias dependencias, vuelve a instalar Composer con PHP 8.4 antes de publicar.

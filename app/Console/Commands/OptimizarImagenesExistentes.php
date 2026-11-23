<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\EmpresaAccesosRapido;
use App\Models\EmpresaServicio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizarImagenesExistentes extends Command
{
    protected $signature = 'sigesga:optimizar-imagenes';
    protected $description = 'Redimensiona y convierte a WebP las imágenes públicas existentes (logo, fondo, servicios y accesos rápidos)';

    public function handle(): int
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
            $this->error('PHP GD con soporte WebP no está disponible en este servidor.');
            return self::FAILURE;
        }

        $optimizadas = 0;

        foreach (Empresa::all() as $empresa) {
            $cambios = [];
            if ($nuevo = $this->convertir($empresa->logo, 512)) {
                $cambios['logo'] = $nuevo;
                $optimizadas++;
            }
            if ($nuevo = $this->convertir($empresa->imagen_fondo, 1600)) {
                $cambios['imagen_fondo'] = $nuevo;
                $optimizadas++;
            }
            if (!empty($cambios)) {
                $empresa->update($cambios);
            }
        }

        foreach (EmpresaServicio::all() as $servicio) {
            if ($nuevo = $this->convertir($servicio->foto, 1200)) {
                $servicio->update(['foto' => $nuevo]);
                $optimizadas++;
            }
        }

        foreach (EmpresaAccesosRapido::all() as $acceso) {
            $ruta = $acceso->imagen;
            if (!$ruta || !str_starts_with($ruta, '/storage/')) {
                continue;
            }
            $rel = substr($ruta, strlen('/storage/'));
            if ($nuevo = $this->convertir($rel, 512)) {
                $acceso->update(['imagen' => '/storage/' . $nuevo]);
                $optimizadas++;
            }
        }

        $this->info("Imágenes optimizadas a WebP: {$optimizadas}");

        return self::SUCCESS;
    }

    private function convertir(?string $rutaRelativa, int $maxWidth): ?string
    {
        if (!$rutaRelativa || str_ends_with(strtolower($rutaRelativa), '.webp')) {
            return null;
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($rutaRelativa)) {
            return null;
        }

        $raw = $disk->get($rutaRelativa);
        $src = @imagecreatefromstring($raw);
        if (!$src) {
            return null;
        }

        try {
            $width = imagesx($src);
            $height = imagesy($src);
            $canvas = $src;

            if ($width > $maxWidth) {
                $newHeight = max(1, (int) round(($height * $maxWidth) / $width));
                $resized = imagecreatetruecolor($maxWidth, $newHeight);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $maxWidth, $newHeight, $transparent);
                imagecopyresampled($resized, $src, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
                $canvas = $resized;
            } else {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
            }

            ob_start();
            $ok = imagewebp($canvas, null, 82);
            $binary = ob_get_clean();

            if ($canvas !== $src) {
                imagedestroy($canvas);
            }

            if (!$ok || !is_string($binary) || strlen($binary) === 0) {
                return null;
            }

            $nuevaRuta = preg_replace('/\.[^.]+$/', '.webp', $rutaRelativa);
            $antesKb = round(strlen($raw) / 1024);
            $despuesKb = round(strlen($binary) / 1024);

            $disk->put($nuevaRuta, $binary);
            if ($nuevaRuta !== $rutaRelativa) {
                $disk->delete($rutaRelativa);
            }

            $this->line("  {$rutaRelativa} ({$antesKb} KB) -> {$nuevaRuta} ({$despuesKb} KB)");

            return $nuevaRuta;
        } finally {
            imagedestroy($src);
        }
    }
}

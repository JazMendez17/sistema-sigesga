<?php

// Servicio de optimización automática de imágenes subidas usando PHP GD (WebP + redimensionado proporcional)

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Optimiza y guarda una imagen subida en el disco público.
     * Redimensiona si supera $maxWidth y convierte a WebP (calidad 82) cuando GD lo soporta.
     */
    public function guardarOptimizada(UploadedFile $file, string $directory, int $maxWidth = 1400, int $quality = 82): string
    {
        if (!function_exists('imagecreatefromstring')) {
            return $file->store($directory, 'public');
        }

        $raw = @file_get_contents($file->getRealPath());
        if ($raw === false) {
            return $file->store($directory, 'public');
        }

        $src = @imagecreatefromstring($raw);
        if (!$src) {
            return $file->store($directory, 'public');
        }

        try {
            $width = imagesx($src);
            $height = imagesy($src);

            if ($width <= 0 || $height <= 0) {
                return $file->store($directory, 'public');
            }

            $canvas = $src;
            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = max(1, (int) round(($height * $maxWidth) / $width));
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
                imagecopyresampled($resized, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                $canvas = $resized;
            } else {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
            }

            $binary = null;
            $extension = null;

            if (function_exists('imagewebp')) {
                ob_start();
                $ok = imagewebp($canvas, null, $quality);
                $data = ob_get_clean();
                if ($ok && is_string($data) && strlen($data) > 0) {
                    $binary = $data;
                    $extension = 'webp';
                }
            }

            if ($binary === null) {
                $mime = strtolower((string) $file->getMimeType());
                ob_start();
                if (str_contains($mime, 'png')) {
                    $ok = imagepng($canvas, null, 9);
                    $extension = 'png';
                } else {
                    $ok = imagejpeg($canvas, null, $quality);
                    $extension = 'jpg';
                }
                $data = ob_get_clean();
                if ($ok && is_string($data) && strlen($data) > 0) {
                    $binary = $data;
                }
            }

            if ($canvas !== $src) {
                imagedestroy($canvas);
            }

            if ($binary === null || $extension === null) {
                return $file->store($directory, 'public');
            }

            $directory = trim($directory, '/');
            $filename = Str::random(40) . '.' . $extension;
            $path = "{$directory}/{$filename}";

            Storage::disk('public')->put($path, $binary);

            return $path;
        } finally {
            imagedestroy($src);
        }
    }
}

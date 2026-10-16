<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Resizes uploaded photos and stores them as WebP on the public disk, using GD.
 */
class ImageOptimizer
{
    /**
     * @return string The stored path on the public disk.
     */
    public function storeAsWebp(UploadedFile $file, string $directory, int $maxDimension = 1600, int $quality = 80): string
    {
        $image = @imagecreatefromstring((string) $file->get());

        if (! $image instanceof GdImage) {
            throw new InvalidArgumentException('The uploaded file is not a supported image.');
        }

        $image = $this->scaleDown($this->applyExifOrientation($image, $file), $maxDimension);

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, $quality);
        $contents = (string) ob_get_clean();

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private function scaleDown(GdImage $image, int $maxDimension): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longestSide = max($width, $height);

        if ($longestSide <= $maxDimension) {
            return $image;
        }

        $scale = $maxDimension / $longestSide;

        return imagescale($image, (int) round($width * $scale), (int) round($height * $scale)) ?: $image;
    }

    /**
     * Phone photos are often stored sideways with an EXIF rotation flag that WebP would lose.
     */
    private function applyExifOrientation(GdImage $image, UploadedFile $file): GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;

        return match ($orientation) {
            3 => imagerotate($image, 180, 0) ?: $image,
            6 => imagerotate($image, -90, 0) ?: $image,
            8 => imagerotate($image, 90, 0) ?: $image,
            default => $image,
        };
    }
}

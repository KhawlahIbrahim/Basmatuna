<?php

namespace App\Services;

use App\Models\Participation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * يعيد ترميز الصورة المرفوعة بالكامل عبر GD:
 * - يتحقق من التوقيع الحقيقي للملف (لا يثق بالامتداد أو MIME القادم من المتصفح)
 * - يزيل بيانات EXIF (ومنها الموقع الجغرافي) وأي حمولة مخفية داخل الملف
 * - يقصّ الصورة مربعًا ويصغّرها ثم يحفظها باسم عشوائي في تخزين خاص
 */
class ImageProcessor
{
    private const ALLOWED = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];

    public function store(UploadedFile $file): string
    {
        ini_set('memory_limit', '256M');

        if (! extension_loaded('gd')) {
            Log::warning('ImageProcessor: GD extension missing');

            throw new RuntimeException('GD extension is required.');
        }

        $bytes = file_get_contents($file->getRealPath());
        $info = $bytes !== false ? @getimagesizefromstring($bytes) : false;

        if (! $info || ! in_array($info[2], self::ALLOWED, true)) {
            Log::warning('ImageProcessor: unsupported image', [
                'size_bytes' => $file->getSize(),
                'detected_type' => $info[2] ?? null,
                'dimensions' => $info ? "{$info[0]}x{$info[1]}" : null,
            ]);

            throw new RuntimeException('Unsupported image.');
        }
        if ($info[0] * $info[1] > 40_000_000) { // حماية من صور ضخمة تستهلك الذاكرة
            Log::warning('ImageProcessor: image too large', [
                'dimensions' => "{$info[0]}x{$info[1]}",
                'pixels' => $info[0] * $info[1],
            ]);

            throw new RuntimeException('Image too large.');
        }

        $src = @imagecreatefromstring($bytes);
        if (! $src) {
            Log::warning('ImageProcessor: corrupted image', [
                'size_bytes' => $file->getSize(),
                'dimensions' => "{$info[0]}x{$info[1]}",
            ]);

            throw new RuntimeException('Corrupted image.');
        }

        $src = $this->applyOrientation($src, $file, $info[2]);

        $size = (int) config('basmah.photo.output_size', 600);
        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $dst = imagecreatetruecolor($size, $size);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, (int) (($w - $side) / 2), (int) (($h - $side) / 2), $size, $size, $side, $side);
        imagedestroy($src);

        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($dst, null, 82);
            $ext = 'webp';
        } else {
            imagejpeg($dst, null, 85);
            $ext = 'jpg';
        }
        $out = ob_get_clean();
        imagedestroy($dst);

        $path = 'participations/'.Str::uuid()->toString().'.'.$ext;
        Storage::disk(Participation::IMAGE_DISK)->put($path, $out);

        return $path;
    }

    private function applyOrientation(\GdImage $img, UploadedFile $file, int $type): \GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $img;
        }
        $exif = @exif_read_data($file->getRealPath());
        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180, 6 => -90, 8 => 90, default => 0,
        };

        return $angle ? (imagerotate($img, $angle, 0) ?: $img) : $img;
    }
}

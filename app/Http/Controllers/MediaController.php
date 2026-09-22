<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    /** صورة عامة: فقط لمشاركة معتمدة وافق صاحبها على عرض صورته */
    public function show(Participation $participation): BinaryFileResponse
    {
        abort_unless($participation->hasPublicImage(), 404);

        return $this->serve($participation, 'public, max-age=86400');
    }

    /** صورة للمشرف: أي مشاركة */
    public function admin(Participation $participation): BinaryFileResponse
    {
        abort_unless($participation->image_path, 404);

        return $this->serve($participation, 'private, no-store');
    }

    private function serve(Participation $participation, string $cache): BinaryFileResponse
    {
        $disk = Storage::disk(Participation::IMAGE_DISK);
        abort_unless($disk->exists($participation->image_path), 404);

        $type = str_ends_with($participation->image_path, '.webp') ? 'image/webp' : 'image/jpeg';

        return response()->file($disk->path($participation->image_path), [
            'Content-Type' => $type,
            'Cache-Control' => $cache,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
        ]);
    }
}

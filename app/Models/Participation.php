<?php

namespace App\Models;

use App\Enums\ParticipationStatus;
use App\Services\StatsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Participation extends Model
{
    public const IMAGE_DISK = 'local'; // تخزين خاص؛ تُقدَّم الصور عبر MediaController فقط

    protected $fillable = [
        'full_name', 'audience_type', 'department', 'selected_word', 'is_custom_word', 'message',
        'image_path', 'show_image', 'status', 'consent', 'consented_at', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'status' => ParticipationStatus::class,
            'consent' => 'boolean',
            'show_image' => 'boolean',
            'is_custom_word' => 'boolean',
            'is_demo' => 'boolean',
            'consented_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => StatsService::flush());
        static::deleted(function (Participation $p) {
            if ($p->image_path) {
                Storage::disk(self::IMAGE_DISK)->delete($p->image_path);
            }
            StatsService::flush();
        });
    }

    /** المشاركات المسموح بعرضها للعامة: معتمدة + موافقة صريحة */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ParticipationStatus::Approved->value)
            ->where('consent', true);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return in_array($status, ParticipationStatus::values(), true)
            ? $query->where('status', $status)
            : $query;
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPublic(): bool
    {
        return $this->status === ParticipationStatus::Approved && $this->consent;
    }

    public function hasPublicImage(): bool
    {
        return $this->isPublic() && $this->show_image && $this->image_path;
    }

    public function publicImageUrl(): ?string
    {
        return $this->hasPublicImage()
            ? route('media.show', ['participation' => $this->id, 'v' => $this->updated_at?->timestamp])
            : null;
    }

    public function adminImageUrl(): ?string
    {
        return $this->image_path ? route('admin.media', $this) : null;
    }

    public function audienceLabel(): string
    {
        return config('basmah.audience_types.'.$this->audience_type, $this->audience_type);
    }

    /** الحرفان الأولان من الاسم لعرضهما بدل الصورة */
    public function initials(): string
    {
        $parts = preg_split('/\s+/u', trim(preg_replace('/^(أ\.|د\.|م\.|أ\.د\.)\s*/u', '', $this->full_name)));
        $letters = array_map(fn ($w) => mb_substr($w, 0, 1), array_slice(array_filter($parts), 0, 2));

        return implode(' ', $letters) ?: '•';
    }

    public function isRecent(): bool
    {
        return $this->created_at && $this->created_at->gt(now()->subDay());
    }

    /** البيانات المسموح بإرسالها للواجهة العامة فقط */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->full_name,
            'department' => $this->department,
            'word' => $this->selected_word,
            'message' => $this->message,
            'image' => $this->publicImageUrl(),
            'initials' => $this->initials(),
            'recent' => $this->isRecent(),
        ];
    }

    public function shortMessage(int $limit = 60): string
    {
        return Str::limit($this->message, $limit);
    }
}

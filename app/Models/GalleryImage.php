<?php

namespace App\Models;

use Database\Factories\GalleryImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A gallery photo stored as WebP on the public disk.
 */
#[Fillable(['path', 'alt', 'category', 'sort_order', 'is_visible'])]
class GalleryImage extends Model
{
    /** @use HasFactory<GalleryImageFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (GalleryImage $image) => Storage::disk('public')->delete($image->path));

        static::updated(function (GalleryImage $image): void {
            if ($image->wasChanged('path')) {
                Storage::disk('public')->delete($image->getOriginal('path'));
            }
        });
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('id');
    }
}

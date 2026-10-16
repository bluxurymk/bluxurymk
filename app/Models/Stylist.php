<?php

namespace App\Models;

use Database\Factories\StylistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'photo', 'bio', 'is_active'])]
class Stylist extends Model
{
    /** @use HasFactory<StylistFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (Stylist $stylist): void {
            if ($stylist->photo) {
                Storage::disk('public')->delete($stylist->photo);
            }
        });

        static::updated(function (Stylist $stylist): void {
            if ($stylist->wasChanged('photo') && $stylist->getOriginal('photo')) {
                Storage::disk('public')->delete($stylist->getOriginal('photo'));
            }
        });
    }

    /**
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * Stylist-specific working hours. When a stylist has none, the
     * salon-wide hours (stylist_id = null) apply.
     *
     * @return HasMany<WorkingHour, $this>
     */
    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    /**
     * @return HasMany<BlockedSlot, $this>
     */
    public function blockedSlots(): HasMany
    {
        return $this->hasMany(BlockedSlot::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}

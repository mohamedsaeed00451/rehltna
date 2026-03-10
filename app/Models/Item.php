<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use softDeletes;
    protected $guarded = [];
    protected $connection = 'tenant';

    public function packages(): HasMany
    {
        return $this->hasMany(ItemPackage::class, 'item_id');
    }
    public function itemType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }

    public function galleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'galleryable')->where('type', 'general');
    }

    public function speakersGalleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'galleryable')->where('type', 'speakers');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(ResidencyUser::class, 'item_residency_users', 'item_id', 'residency_user_id')
            ->withPivot('id', 'attendees')
            ->withTimestamps();
    }

    protected $appends = ['coupons'];

    protected $hidden = ['assignedCoupons'];

    public function assignedCoupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_items');
    }

    public function getCouponsAttribute()
    {
        $validSpecificCoupons = $this->assignedCoupons->filter(function ($coupon) {
            $isActive = $coupon->status == 1;
            $isValidDate = $coupon->expires_at === null || $coupon->expires_at >= now();

            return $isActive && $isValidDate;
        });

        if ($validSpecificCoupons->isNotEmpty()) {
            return $validSpecificCoupons->values();
        }

        static $globalCoupons = null;
        if ($globalCoupons === null) {
            $globalCoupons = Coupon::query()
                ->doesntHave('items')
                ->where('status', 1)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->get();
        }
        return $globalCoupons;
    }

    public function getBannerEnAttribute($value): null|string
    {
        return $value ? asset($value) : null;
    }

    public function getBannerArAttribute($value): null|string
    {
        return $value ? asset($value) : null;
    }

    public function getMetaImgAttribute($value): null|string
    {
        return $value ? asset($value) : null;
    }

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('l, d F Y - H:i A');
    }

    public function getUpdatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('l, d F Y - H:i A');
    }

}

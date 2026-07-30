<?php

namespace App\Models;

use App\Services\ImageUploadService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 事業所・勤務地。掲載企業が持つ拠点(特養・デイサービスなどの単位)。
 * 求人はここに紐づき、住所・アクセス・施設形態を引き継ぐ(SPEC.md 11.6)。
 */
class Workplace extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'facility_type_id', 'name',
        'postal_code', 'prefecture_id', 'city_id', 'address', 'access',
        'photo_path', 'description',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function facilityType(): BelongsTo
    {
        return $this->belongsTo(FacilityType::class);
    }

    public function prefecture(): BelongsTo
    {
        return $this->belongsTo(Prefecture::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    /** 一覧表示用の所在地(都道府県+市区町村)。 */
    public function locationLabel(): ?string
    {
        if (! $this->prefecture) {
            return null;
        }

        return $this->prefecture->name.($this->city?->name ?? '');
    }

    public function photoUrl(): ?string
    {
        return ImageUploadService::url($this->photo_path);
    }

    /** この事業所に求人が紐づいているか。削除の可否判定に使う。 */
    public function hasJobPostings(): bool
    {
        return $this->jobPostings()->exists();
    }
}

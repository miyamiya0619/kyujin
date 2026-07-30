<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * 掲載企業の担当者。**自社の求人と応募者だけを扱える。**
 */
class CompanyUser extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_OWNER = 'owner';

    public const ROLE_MEMBER = 'member';

    protected $fillable = ['company_id', 'name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** 担当者の追加・削除ができるか。 */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token, 'company.password.reset'));
    }
}

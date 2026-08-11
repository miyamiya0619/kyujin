<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * 求職者(メディアに会員登録して応募する人)。
 *
 * ⚠ **健康状態・病歴・障害などの要配慮個人情報を持たせてはいけない**(CLAUDE.md 3.4)。
 *
 * メール認証(T-27)が済むまでは `verified` ミドルウェアでマイページ等への
 * アクセスを止める。応募(`public.jobs.apply.store`)はこのミドルウェアの
 * 対象外なので、未認証でも応募自体は完了する。
 */
class JobSeeker extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory, MustVerifyEmail, Notifiable;

    protected $fillable = [
        'name', 'name_kana', 'email', 'password', 'tel', 'birthday',
        'prefecture_id', 'city_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birthday' => 'date',
            'email_verified_at' => 'datetime',
        ];
    }

    public function prefecture(): BelongsTo
    {
        return $this->belongsTo(Prefecture::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function qualifications(): BelongsToMany
    {
        return $this->belongsToMany(Qualification::class, 'job_seeker_qualification');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(JobSeekerExperience::class)->orderBy('sort_order');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token, 'seeker.password.reset'));
    }
}

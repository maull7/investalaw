<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'permissions', 'institution', 'position', 'province', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @return HasMany<ReviewDocument> */
    public function reviewDocuments(): HasMany
    {
        return $this->hasMany(ReviewDocument::class);
    }

    /** @return HasMany<UserPackage> */
    public function userPackages(): HasMany
    {
        return $this->hasMany(UserPackage::class);
    }

    /** @return HasMany<UserActivityLog> */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /** @return HasMany<Review> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function isReviewer(): bool
    {
        return $this->role === 'reviewer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSubAdmin(): bool
    {
        return $this->role === 'sub_admin';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isSubAdmin() && in_array($permission, $this->permissions ?? []);
    }

    public function hasCompletedProfile(): bool
    {
        return $this->role !== 'user'
            || ($this->institution && $this->position && $this->province && $this->phone);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }
}

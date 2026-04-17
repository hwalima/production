<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'job_title',
        'avatar_path',
        'force_password_change',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at'      => 'datetime',
        'force_password_change'  => 'boolean',
        'is_active'              => 'boolean',
    ];

    /* ── Role helpers ─────────────────────────────────── */

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isManager(): bool    { return $this->role === 'manager'; }
    public function isViewer(): bool     { return $this->role === 'viewer'; }

    /** True when the user holds admin-level privileges (super_admin or admin). */
    public function isAdminOrAbove(): bool { return $this->hasAnyRole('super_admin', 'admin'); }

    /** True if the user's role is in the given list. */
    public function hasAnyRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /** Viewers can only read; managers and above can write. */
    public function canWrite(): bool { return $this->hasAnyRole('super_admin', 'admin', 'manager'); }
}

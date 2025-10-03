<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * User accounts never expire - they remain active permanently
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_picture',
        'last_login_at',
        'last_login_ip',
        'last_user_agent',
    ]; 
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Check if account is expired (always returns false - user accounts never expire)
     */
    public function isExpired()
    {
        return false;
    }

    /**
     * Check if account is active (always returns true - user accounts never expire)
     */
    public function isActive()
    {
        return true;
    }

    public function employee()
    {
        return $this->hasOne(\App\Models\Employee::class, 'email', 'email');
    }

    /**
     * Get the admin login sessions for this user
     */
    public function adminLoginSessions()
    {
        return $this->hasMany(\App\Models\AdminLoginSession::class, 'user_id');
    }

    /**
     * Get the active admin login sessions for this user
     */
    public function activeAdminLoginSessions()
    {
        return $this->hasMany(\App\Models\AdminLoginSession::class, 'user_id')
                    ->where('is_active', true);
    }
}

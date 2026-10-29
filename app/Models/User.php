<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements LdapAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    public function dashboardUrl()
    {
        return match ($this->role) {
            'admin'         => '/admin/dashboard',
            'administrator' => '/administrator/dashboard',
            'mahasiswa'     => '/mahasiswa/dashboard',
            'kaprodi'       => '/kaprodi/dashboard',
            'dekan'         => '/dekan/dashboard',
            'dosen'         => '/dosen/dashboard',
            default         => '/',
        };
    }

    /**
     * Cek apakah user memiliki salah satu role yang diberikan.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Cek apakah user memiliki salah satu role dari array.
     *
     * @param  array<int, string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Role yang boleh melihat/mengelola seluruh surat pengajuan
     * (bukan hanya miliknya sendiri).
     */
    public function isStaffSurat(): bool
    {
        return $this->hasAnyRole(['admin', 'kaprodi', 'dekan']);
    }

    protected $fillable = [
        'name',
        'username',
        'email',
        'emailyarsi',
        'password',
        'role',
        'prodi',
        'npm',
        'nidn',
        'serial_number',
        'telephonenumber'
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
        ];
    }
}

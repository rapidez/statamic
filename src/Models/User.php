<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Statamic\Notifications\PasswordReset;

class User extends Authenticatable
{
    use Notifiable;

    protected array $fillable = [
        'name',
        'email',
        'password',
    ];

    protected array $hidden = [
        'password',
        'remember_token',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordReset($token));
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

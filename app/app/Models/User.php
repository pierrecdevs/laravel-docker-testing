<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'avkey',
        'email',
        'password',
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

    public function getFullnameAttribute()
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function loginTokens()
    {
        return $this->hasMany(LoginToken::class);
    }

    /**
     * NOTE: This is for SL ... for now it'll be commented out.
     * public function generateTokenUrl(string $avkey, string $firstname, string $lastname)
     *
     */
    public function generateTokenUrl()
    {
        /*$combined = implode('.', [$avkey, $firstname, $lastname]);*/
        $plaintext = Str::random(32);
        /*$plaintext = Crypt::encrypt($combined);*/
        $expires_at = now()->addMinutes(15);
        $this->loginTokens()->create([
            'token' => hash('sha256', $plaintext),
            'expires_at' => $expires_at,
        ]);


        return URL::temporarySignedRoute('auth.verify', $expires_at, ['token' => $plaintext]);
    }
}

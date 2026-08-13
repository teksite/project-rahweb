<?php

namespace Lareon\Modules\User\App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Sanctum\HasApiTokens;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\User\Database\Factories\UserFactory;
use Teksite\Authorize\Traits\HasAuthorization;
use Teksite\Extralaravel\Enums\MobilePatterns;
use Teksite\Extralaravel\Rules\MobileRule;
use Teksite\Extralaravel\Traits\MustVerifyPhone;

#[UseFactory(UserFactory::class)]
#[Fillable(['name', 'lastname', 'email', 'phone', 'password', 'slug' ,'parent_id'])]
#[Hidden(['password', 'remember_token' ,'two_factor_secret','two_factor_recovery_codes','two_factor_confirmed_at'])]
class User extends Authenticatable implements MustVerifyEmail , PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasAuthorization ,MustVerifyPhone ,TwoFactorAuthenticatable , HasApiTokens , PasskeyAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public static function rules(string $operation, int|null $userId = null): array
    {
        return match (true) {
            $operation === 'create'=> [
                'name'     => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'password' => 'required|string|min:6|confirmed',
                'phone'    => ['required','unique:users', 'string', new MobileRule(MobilePatterns::IRAN)],
                'email'    => 'required|string|email|max:255|unique:users',

            ],
            ($operation === 'update' && $userId) => [
                'name'     => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'password' => 'nullable|string|min:6|confirmed',
                'phone'    => ['required', 'string', new MobileRule(MobilePatterns::IRAN), Rule::unique('users', 'phone')->ignore($userId)],
                'email'    => ['required', 'string', 'email', Rule::unique('users', 'email')->ignore($userId)],
            ],
            default=> throw new \InvalidArgumentException("Operation '{$operation}' is not valid. Allowed: create, update")
        };
    }


    public function path(): ?string
    {
        return Route::has('users.show') ? route('users.show', ['user' => $this]) : null;
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->hasMany(User::class, 'parent_id');
    }


    public function sendPhoneVerificationNotification(): void
    {
        // TODO: Implement sendPhoneVerificationNotification() method.
    }

    public function fullname(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst($this->name) . ' ' . ucfirst($this->lastname)
        );
    }


    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

}

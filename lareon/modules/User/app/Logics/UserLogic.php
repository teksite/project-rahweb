<?php

namespace Lareon\Modules\User\App\Logics;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Lareon\Modules\User\App\Models\User;
use Teksite\Authorize\Models\Role;
use Teksite\Handler\Actions\ServiceWrapper;
use Teksite\Handler\contracts\ServiceResult;
use Teksite\Handler\Services\FetchDataService;


class UserLogic
{
    /**
     * @throws \Throwable
     */
    public function all(mixed $fetchData = []): ServiceResult
    {
        return ServiceWrapper::make(false)
                             ->do(fn() => FetchDataService::get(User::class, ['name', 'lastname', 'email', 'phone']))
                             ->run();
    }

    /**
     * @throws \Throwable
     */
    public function allByParent(mixed $fetchData = []): ServiceResult
    {
        return ServiceWrapper::make(false)
                             ->do(fn() => FetchDataService::get(auth()->user()->children(), ['name', 'lastname', 'email', 'phone']))
                             ->run();
    }


    /**
     * @throws BindingResolutionException
     * @throws \Throwable
     */
    public function first(array $inputs = [], bool $any = true): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($inputs) {
            $query = User::query();
            foreach ($inputs as $key => $value) {
                $query->where($key, $value);
            }
        })->run();
    }

    /**
     * @throws \Throwable
     */
    public function create(array $inputs = []): ServiceResult
    {
        return ServiceWrapper::make(true)->do(function () use ($inputs) {
            $inputs['slug'] ??= strtolower(uniqid() . '-' . Str::random(4));
            $inputs['parent_id'] = auth()->id();
            // TODO remove below code and remove from User fillable
            $inputs['phone_verified_at']=now();
            $inputs['email_verified_at']=now();
            $user = User::create($inputs);

            $rolesIds = $this->assignRole($user, config('general.default_user_role', 'user'));
            return $user;
        })->run();
    }

    /**
     * @throws \Throwable
     */
    public function update(Authenticatable|User $user, array $inputs = []): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($user, $inputs) {
            if (!isset($inputs['password']) || $inputs['password'] === null)  unset($inputs['password']);

            $user->fill(Arr::except($inputs, ['permissions', 'roles', 'enable_2fa', 'meta', 'seo']));
            $this->toggle2fa($user, $inputs['enable_2fa'] ?? null);
            $user->save();
            return $user->refresh();
        })->run();
    }


    /**
     * @throws \Throwable
     */
    public function changePassword(Authenticatable|User $user, array $inputs = []): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($user, $inputs) {
         $user->update(['password' =>$inputs['password']]);
        })->run();
    }

    /**
     * @throws \Throwable
     */
    public function delete(Authenticatable|User $user): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($user) {
            $user->roles()->detach();
            $user->delete();
        })->run();
    }

    /**
     * @throws \Throwable
     */
    public function markAsVerified(Authenticatable|User $user, int|null|bool $email = -1, int|null|bool $phone = -1): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($phone, $email, $user) {
            $cols = [
                ['field' => 'email', 'column' => 'email_verified_at', 'value' => $email],
                ['field' => 'phone', 'column' => 'phone_verified_at', 'value' => $phone],
            ];
            $res = [];
            foreach ($cols as ['field' => $field, 'column' => $column, 'value' => $value]) {
                if (is_null($value) || $value === -1) continue;

                if ($user->$column !== null && ($value === 0 || $value === false)) {
                    $res[$column] = null;
                } elseif ($user->$column === null && ($value === 1 || $value === true)) {
                    $res[$column] = now();
                }
            }
            if (empty($res)) return $res;
            $user->forceFill($res)->save();

            return $res;
        })->run();
    }

    /**
     * @throws \Throwable
     */
    public function assignRole(Authenticatable|User $user, string|int|Role $inputs, string $action = 'creating'): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($inputs, $action, $user) {
            $roleArray = $user->assignRole($inputs);
            if (empty($roleArray)) {
                throw new \Exception("the user with id: " . $user->id . " has no role => attaching role is failed in $action the user");
            }
        })->run();
    }


    /**
     * @throws BindingResolutionException
     * @throws \Throwable
     */
    public function updateACL(Authenticatable|User $user, array $inputs)
    {
        return ServiceWrapper::make(false)->do(function () use ($inputs, $user) {
            $roles = $inputs['roles'] ?? [];
            $permissions = $inputs['permissions'] ?? [];
            $roleArray = $user->assignRole($roles);
            $permissionsArray = $user->syncPermissions($permissions);

            return [
                'roles'       => $roleArray,
                'permissions' => $permissionsArray,
            ];

        })->run();
    }


    public function toggle2fa(Authenticatable|User $user, int|null $status = null): void
    {
        if ($status === 0) {
            $user->forceFill([
                'two_factor_secret'         => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at'   => null,
            ]);
        }
    }

}


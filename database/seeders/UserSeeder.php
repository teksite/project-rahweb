<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Lareon\Modules\User\App\Models\User;
use Teksite\Authorize\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superUser =$this->makeSuperUser();
        $this->makeTicketManager($superUser);
        $this->makeFakeUser($superUser);
    }

    private function makeSuperUser()
    {
        $user = User::query()->create([
            'name'     => 'sina',
            'lastname' => 'Zangiband',
            'email'    => 'zb.sina@teksite.net',
            'password' => Hash::make('zb.sina@teksite.net'),
            'phone'    => '989126037279',
            'slug'     => '989126037279',

        ]);

        $user->markEmailAsVerified();
        $user->markPhoneAsVerified();
        $ownerRole = Role::query()->firstWhere('title', 'owner');

        if ($ownerRole) $user->roles()->sync($ownerRole->id);

        return $user;
    }

    private function makeTicketManager(User $superUser)
    {
        $user1=User::query()->create([
            'name'     => 'ticket manager',
            'lastname' => 'ticketing',
            'email'    => 'user1@example.com',
            'password' => Hash::make('user1@example.com'),
            'phone'    => '09126060606',
            'slug'     => '09126060606',
        ]);
        $user1->markEmailAsVerified();
        $user1->markPhoneAsVerified();

        $user2=User::query()->create([
            'name'     => 'chief ticket manager',
            'lastname' => 'ticketing',
            'email'    => 'user2@example.com',
            'password' => Hash::make('user2@example.com'),
            'phone'    => '09126060607',
            'slug'     => '09126060607',
        ]);
        $user2->markEmailAsVerified();
        $user2->markPhoneAsVerified();

        $chief = Role::query()->firstWhere('title', config('ticketing.admin.level_1' , 'ticket manager'))->id;
        $manager = Role::query()->firstWhere('title', config('ticketing.admin.level_2' , 'chief ticket manager'))->id;

        $user1->roles()->sync([$manager]);
        $user2->roles()->sync([$chief]);
    }

    private function makeFakeUser($superUser): void
    {
        $userRole = Role::query()->firstWhere('title', 'user');

        $user3 = User::create([
            'parent_id' => $superUser->id,
            'name'     => 'normal user',
            'lastname' => 'global',
            'email'    => 'user3@example.com',
            'password' => Hash::make('user3@example.com'),
            'phone'    => '09126060608',
            'slug'     => '09126060608',
        ]);
        $user3->markEmailAsVerified();
        $user3->markPhoneAsVerified();
        $user3->roles()->attach($userRole->id);

        $users = User::factory(45)->create([
            'parent_id' => $superUser->id,
        ]);

        foreach ($users as $newUser) {
            $newUser->roles()->attach($userRole->id);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Domains\Identity\Domain\Enums\UserRole;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 3 Admin Users
        User::factory(3)->create([
            'role' => UserRole::ADMIN->value,
        ]);

        // 5 Customer Users
        User::factory(5)->create([
            'role' => UserRole::CUSTOMER->value,
        ]);
    }
}

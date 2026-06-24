<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@cuaca.test'],
            ['name' => 'Administrator', 'password' => Hash::make('password')]
        );
        $admin->assignRole('super_admin');

        $operator = User::firstOrCreate(
            ['email' => 'operator@cuaca.test'],
            ['name' => 'Operator', 'password' => Hash::make('password')]
        );
        $operator->assignRole('operator');
    }
}

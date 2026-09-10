<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'yamada@example.com'],
            [
                'name' => '山田太郎',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'suzuki@example.com'],
            [
                'name' => '鈴木花子',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'tanaka@example.com'],
            [
                'name' => '田中一郎',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'sato@example.com'],
            [
                'name' => '佐藤美咲',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'takahashi@example.com'],
            [
                'name' => '高橋健太',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}

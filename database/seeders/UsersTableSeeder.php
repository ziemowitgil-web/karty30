<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Ewelina Testowa',
                'email' => 'ewelina@testy.feer.org.pl',
                'password' => Hash::make('Y)%[C!y@<6yZ7h-2cpBYVtLWq'),
                'role' => 'user', // opcjonalnie, jeśli masz kolumnę role
            ],
            [
                'name' => 'Developer Admin',
                'email' => 'developeradmin@testy.feer.org.pl',
                'password' => Hash::make('D3vAdm!n2025'),
                'role' => 'admin', // opcjonalnie
            ],

            [
                'name' => 'Ziemowit  testy ',
                'email' => 'ziemowit@testy.feer.org.pl',
                'password' => Hash::make('zaq1@WSX'),
                'role' => 'admin', // opcjonalnie
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']], // unikamy duplikatów
                $user
            );
        }
    }
}

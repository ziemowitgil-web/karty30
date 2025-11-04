<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;

class SchedulesTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $clients = Client::all();

        if($users->isEmpty() || $clients->isEmpty()) {
            $this->command->info('Brak użytkowników lub klientów. Seeder nie został wykonany.');
            return;
        }

        // Tworzymy 7 zapisów dla różnych klientów
        $clients->take(7)->each(function($client) use ($users) {
            Schedule::create([
                'client_id' => $client->id,
                'user_id' => $users->random()->id,
                'start_time' => Carbon::now()->addDays(rand(1, 10))->setTime(rand(8, 16), 0),
                'duration_minutes' => [30, 45, 60][array_rand([30,45,60])],
                'description' => 'Automatyczna rezerwacja testowa',
                'status' => 'preliminary',
                'approved_by_name' => null,
            ]);
        });
    }
}

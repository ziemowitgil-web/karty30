<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Consultation;
use App\Models\Client;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;

class ConsultationsTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $users = User::all();
        $schedules = Schedule::all();

        if($clients->isEmpty() || $users->isEmpty()) {
            $this->command->info('Brak klientów lub użytkowników – seeder nie został wykonany.');
            return;
        }

        // Tworzymy 7 testowych konsultacji
        $clients->take(7)->each(function($client, $index) use ($users, $schedules) {
            $user = $users->random();
            $schedule = $schedules->random() ?? null;

            Consultation::create([
                'client_id' => $client->id,
                'user_id' => $user->id,
                'consultation_datetime' => Carbon::now()->addDays(rand(1,10))->setTime(rand(8,16), 0),
                'duration_minutes' => [30, 45, 60][array_rand([30,45,60])],
                'description' => "Testowa konsultacja nr " . ($index+1),
                'status' => 'draft',
                'approved_by_name' => $user->name,
                'confirmed' => (bool)rand(0,1),
                'next_action' => "Kolejna wizyta za tydzień",
                'user_email' => $user->email,
                'username' => $user->name,
                'user_ip' => '127.0.0.1',
                'sha1sum' => sha1("Consultation".$index),
            ]);
        });
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Anna Kowalska',
                'email' => 'anna.kowalska@example.com',
                'phone' => '+48123456789',
                'status' => 'enrolled',
                'problem' => 'Rehabilitacja kończyny dolnej',
                'equipment' => 'Wózek inwalidzki',
                'date_of_birth' => '1985-03-15',
                'gender' => 'female',
                'address' => 'ul. Polna 12, 00-001 Warszawa',
                'notes' => 'Preferuje kontakt telefoniczny po południu',
                'preferred_contact_method' => 'phone',
                'consent' => true,
                'available_days' => ['Mon','Wed','Fri'],
                'time_slots' => ['10:00-11:00','14:00-15:00'],
            ],
            [
                'name' => 'Jan Nowak',
                'email' => 'jan.nowak@example.com',
                'phone' => '+48111222333',
                'status' => 'ready',
                'problem' => 'Kontuzja ręki',
                'equipment' => 'Kule',
                'date_of_birth' => '1990-07-21',
                'gender' => 'male',
                'address' => 'ul. Lipowa 5, 00-002 Warszawa',
                'notes' => 'Kontakt mailowy rano',
                'preferred_contact_method' => 'email',
                'consent' => true,
                'available_days' => ['Tue','Thu'],
                'time_slots' => ['09:00-10:00','15:00-16:00'],
            ],
            [
                'name' => 'Ewa Wiśniewska',
                'email' => 'ewa.wisniewska@example.com',
                'phone' => '+48987654321',
                'status' => 'to_settle',
                'problem' => 'Rehabilitacja kręgosłupa',
                'equipment' => 'Materac ortopedyczny',
                'date_of_birth' => '1988-12-02',
                'gender' => 'female',
                'address' => 'ul. Brzozowa 7, 00-003 Warszawa',
                'notes' => 'Preferuje kontakt SMS',
                'preferred_contact_method' => 'sms',
                'consent' => true,
                'available_days' => ['Mon','Tue','Wed'],
                'time_slots' => ['08:00-09:00','12:00-13:00'],
            ],
            [
                'name' => 'Marek Lewandowski',
                'email' => 'marek.lewandowski@example.com',
                'phone' => '+48123498765',
                'status' => 'other',
                'problem' => 'Utrata równowagi',
                'equipment' => 'Laska',
                'date_of_birth' => '1975-06-30',
                'gender' => 'male',
                'address' => 'ul. Dębowa 10, 00-004 Warszawa',
                'notes' => 'Kontakt telefoniczny wieczorem',
                'preferred_contact_method' => 'phone',
                'consent' => true,
                'available_days' => ['Thu','Fri'],
                'time_slots' => ['16:00-17:00','18:00-19:00'],
            ],
            [
                'name' => 'Katarzyna Zielińska',
                'email' => 'katarzyna.zielinska@example.com',
                'phone' => '+48112233445',
                'status' => 'enrolled',
                'problem' => 'Rehabilitacja nogi',
                'equipment' => 'Wózek inwalidzki',
                'date_of_birth' => '1992-04-11',
                'gender' => 'female',
                'address' => 'ul. Słoneczna 3, 00-005 Warszawa',
                'notes' => 'Kontakt mailowy po południu',
                'preferred_contact_method' => 'email',
                'consent' => true,
                'available_days' => ['Mon','Wed','Fri'],
                'time_slots' => ['10:00-11:00','13:00-14:00'],
            ],
            [
                'name' => 'Piotr Kamiński',
                'email' => 'piotr.kaminski@example.com',
                'phone' => '+48115566778',
                'status' => 'ready',
                'problem' => 'Kontuzja barku',
                'equipment' => 'Orteza',
                'date_of_birth' => '1980-09-19',
                'gender' => 'male',
                'address' => 'ul. Wrzosowa 8, 00-006 Warszawa',
                'notes' => 'Preferuje kontakt telefoniczny rano',
                'preferred_contact_method' => 'phone',
                'consent' => true,
                'available_days' => ['Tue','Thu'],
                'time_slots' => ['09:00-10:00','11:00-12:00'],
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}

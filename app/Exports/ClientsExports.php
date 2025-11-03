<?php
// app/Exports/ClientsExport.php
namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Client::all([
            'id', 'name', 'email', 'phone', 'status', 'gender',
            'date_of_birth', 'address', 'preferred_contact_method'
        ]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Imię i nazwisko',
            'Email',
            'Telefon',
            'Status',
            'Płeć',
            'Data urodzenia',
            'Adres',
            'Preferowany kontakt',
        ];
    }
}

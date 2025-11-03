<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

## Komit

class ExcelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // tylko zalogowani mogą eksportować
    }

    /**
     * Eksport listy klientów do XLS
     */
    public function exportClients()
    {
        $fileName = 'klienci_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new class implements FromCollection, WithHeadings {
            public function collection()
            {
                // Pobieramy wszystkich klientów i ich pola
                return Client::select(
                    'id',
                    'name',
                    'email',
                    'phone',
                    'status',
                    'gender',
                    'date_of_birth',
                    'address',
                    'preferred_contact_method',
                    'consent',
                    'created_at'
                )->get();
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
                    'Zgoda na przetwarzanie danych',
                    'Data utworzenia',
                ];
            }
        }, $fileName);
    }
}

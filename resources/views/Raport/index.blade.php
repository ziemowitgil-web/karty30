@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-extrabold mb-12 text-center text-gray-800">Raporty</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @php
                $reports = [
                    [
                        'title' => 'Raport odwołanych terminów',
                        'route' => 'raports.cancelled',
                        'icon'  => '📅',
                        'gradient' => 'from-blue-400 to-blue-600',
                    ],
                    [
                        'title' => 'Raport konsultacji w tym miesiącu',
                        'route' => 'raports.approvedThisMonth',
                        'icon'  => '✅',
                        'gradient' => 'from-green-400 to-green-600',
                    ],
                    [
                        'title' => 'Raport konsultacji w poprzednim miesiącu',
                        'route' => 'raports.approvedLastMonth',
                        'icon'  => '🕒',
                        'gradient' => 'from-green-300 to-green-500',
                    ],
                    [
                        'title' => 'Raport konsultacji do MRPiPS (PDF)',
                        'route' => 'raports.monthlyReportMRPIPS',
                        'icon'  => '📄',
                        'gradient' => 'from-pink-400 to-pink-600',
                    ],
                    [
                        'title' => 'Czarna lista (CL)',
                        'route' => 'raports.blacklist',
                        'icon'  => '⛔',
                        'gradient' => 'from-red-400 to-red-600',
                    ],
                ];
            @endphp

            @foreach ($reports as $report)
                <a href="{{ route($report['route']) }}"
                   class="relative flex flex-col justify-between p-6 rounded-2xl shadow-2xl bg-gradient-to-r {{ $report['gradient'] }} text-white hover:scale-105 transition-transform duration-300">

                    <!-- Ikona i tytuł -->
                    <div class="flex items-center mb-4">
                        <div class="text-5xl mr-4">{{ $report['icon'] }}</div>
                        <h2 class="text-xl font-bold">{{ $report['title'] }}</h2>
                    </div>

                    <!-- Mini wizualizacja / placeholder -->
                    <div class="w-full h-16 bg-white/20 rounded-lg flex items-center justify-center text-white/80 text-sm font-medium">
                        Tutaj mogą być dane / wykres
                    </div>

                    <!-- Przycisk lub wskaźnik -->
                    <div class="mt-4 text-right">
                        <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">Zobacz raport</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('home') }}"
               class="inline-block bg-gray-700 text-white px-8 py-3 rounded-lg shadow hover:bg-gray-800 transition duration-300 font-medium">
                Powrót do strony głównej
            </a>
        </div>
    </div>
@endsection

@component('mail::message')
    # Raport MRPiPS

    W załączeniu przesyłam raport miesięczny zatwierdzonych konsultacji.

    - Miesiąc: {{ $month }}/{{ $year }}
    - Liczba konsultacji: {{ $consultations->count() }}
    - Wygenerowano: {{ now()->format('d.m.Y H:i') }}
    - Wygenerował: {{ $generated_by }}

@endcomponent

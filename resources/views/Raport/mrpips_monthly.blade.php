<!-- resources/views/emails/reports/monthly.blade.php -->

<p>W załączeniu przesyłam raport miesięczny zatwierdzonych konsultacji.</p>

<ul>
    <li>Miesiąc: {{ $month }}/{{ $year }}</li>
    <li>Liczba konsultacji: {{ $consultations->count() }}</li>
    <li>Wygenerowano: {{ $generated_at }}</li>
    <li>Wygenerował: {{ $generated_by }}</li>
</ul>

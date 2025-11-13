<!-- resources/views/emails/reports/monthly.blade.php -->

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Raport miesięczny</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
        }
        h2 {
            color: #1f2937;
            font-size: 20px;
            margin-bottom: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 12px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Raport miesięczny zatwierdzonych konsultacji</h2>

    <p>Poniżej znajdują się informacje dotyczące raportu:</p>

    <table>
        <tr>
            <th>Miesiąc</th>
            <td>{{ $month }}/{{ $year }}</td>
        </tr>
        <tr>
            <th>Liczba konsultacji</th>
            <td>{{ $consultations->count() }}</td>
        </tr>
        <tr>
            <th>Wygenerowano</th>
            <td>{{ $generated_at }}</td>
        </tr>
        <tr>
            <th>Wygenerował</th>
            <td>{{ $generated_by }}</td>
        </tr>
    </table>

    @if($consultations->isNotEmpty())
        <h3 style="margin-top: 20px;">Szczegóły konsultacji</h3>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Klient</th>
                <th>Data i godzina</th>
                <th>Czas trwania (min)</th>
                <th>Przeprowadził</th>
                <th>Opis</th>
            </tr>
            </thead>
            <tbody>
            @foreach($consultations as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->client->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}</td>
                    <td>{{ $c->duration_minutes }}</td>
                    <td>{{ $c->user->name ?? '-' }}</td>
                    <td>{{ Str::limit($c->description, 50) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer">Ten raport został wygenerowany automatycznie. Prosimy nie odpowiadać na tę wiadomość.</p>
</div>
</body>
</html>

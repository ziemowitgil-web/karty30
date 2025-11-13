<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12pt;
            margin: 40px;
            color: #000;
            background-color: #fff;
        }

        .organization {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #003366;
        }

        .header {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 20px;
            color: #003366;
            border-bottom: 2px solid #003366;
            padding-bottom: 5px;
        }

        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #f8f8f8;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #aaa;
            padding-bottom: 5px;
            font-size: 13.5pt;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
        }

        .description {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #fff;
            min-height: 50px;
        }

        .footer {
            font-size: 9pt;
            text-align: center;
            color: #666;
            margin-top: 30px;
        }

        .log-section {
            font-size: 10pt;
            color: #444;
            background-color: #f1f1f1;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .qr-code {
            text-align: right;
            margin-bottom: 20px;
        }

        .qr-code img {
            width: 100px;
            height: 100px;
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="organization">FEER</div>
<div class="header">Karta konsultacyjna</div>

<!-- QR kod -->
@if(!empty($qrImage))
    <div class="qr-code">
        <img src="data:image/png;base64,{{ $qrImage }}" alt="QR Code">
    </div>
@endif

<!-- Informacje o konsultacji -->
<div class="section">
    <div class="section-title">Informacje o kliencie i konsultacji</div>
    <div><span class="label">Klient:</span> {{ $consultation->client->name ?? '-' }}</div>
    <div><span class="label">Data i godzina:</span> {{ \Carbon\Carbon::parse($consultation->consultation_datetime)->format('d.m.Y H:i') ?? '-' }}</div>
    <div><span class="label">Czas trwania:</span> {{ $consultation->duration_minutes ?? '-' }} min</div>
    <div><span class="label">Status:</span> {{ $status ?? '-' }}</div>
    <div><span class="label">Przeprowadzono przez:</span> {{ $conductedBy ?? '-' }}</div>
    <div><span class="label">Zatwierdził:</span> {{ $approvedBy ?? '-' }}</div>
    <div><span class="label">IP użytkownika:</span> {{ $ipFormatted ?? '-' }} <small>({{ $ipRaw ?? '-' }})</small></div>
    <div><span class="label">Data wydruku:</span> {{ $printDateTime ?? '-' }}</div>
    <div><span class="label">Miesiąc sprawozdawczy:</span> {{ $reportMonth ?? '-' }}</div>
</div>

<!-- Opis konsultacji -->
<div class="section">
    <div class="section-title">Opis konsultacji</div>
    <div class="description">{{ $consultation->description ?? '-' }}</div>
</div>

<!-- Historia logów -->
<div class="section">
    <div class="section-title">Historia akcji / logi</div>
    <div class="log-section">
        @foreach($consultation->activities ?? [] as $activity)
            <div>
                <strong>{{ \Carbon\Carbon::parse($activity->created_at)->format('d.m.Y H:i') }}</strong>:
                {{ $activity->description }}
                @if(isset($activity->causer->name)) - {{ $activity->causer->name }} @endif
            </div>
        @endforeach
        @if(empty($consultation->activities))
            Brak logów dla tej konsultacji.
        @endif
    </div>
</div>

<!-- Dane podpisu cyfrowego -->
@if(!empty($certificate))
    <div class="section">
        <div class="section-title">Podpis cyfrowy</div>
        <div><span class="label">Common Name (CN):</span> {{ $certificate['CN'] ?? $certificate['common_name'] ?? '-' }}</div>
        <div><span class="label">E-mail:</span> {{ $certificate['email'] ?? '-' }}</div>
        <div><span class="label">Organizacja (O):</span> {{ $certificate['O'] ?? $certificate['organization'] ?? '-' }}</div>
        <div><span class="label">Jednostka organizacyjna (OU):</span> {{ $certificate['OU'] ?? $certificate['organizational_unit'] ?? '-' }}</div>
        <div><span class="label">Data ważności od:</span> {{ $certificate['valid_from'] ?? '-' }}</div>
        <div><span class="label">Data ważności do:</span> {{ $certificate['valid_to'] ?? '-' }}</div>
        <div><span class="label">SHA1 certyfikatu:</span> {{ $certificate['sha1'] ?? '-' }}</div>
        @if(isset($certificate['is_test_certificate']) && $certificate['is_test_certificate'])
            <div style="color:red;"><strong>Certyfikat testowy</strong></div>
        @endif
    </div>
@endif

<div class="footer">
    Dokument wewnętrzny do sprawozdawczości | RODO: zgoda | PDF wygenerowany automatycznie | FEER
</div>

</body>
</html>

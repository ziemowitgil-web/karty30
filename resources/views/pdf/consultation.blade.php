<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12pt;
        margin: 40px;
        color: #000;
        background-color: #fff;
    }

    .header {
        text-align: center;
        font-size: 18pt;
        font-weight: bold;
        color: #003366;
        margin-bottom: 15px;
        border-bottom: 2px solid #003366;
        padding-bottom: 5px;
    }

    .subheader {
        font-size: 14pt;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
    }

    .section {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #333;
        border-radius: 6px;
        background-color: #fdfdfd;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 10px;
        font-size: 13.5pt;
        border-bottom: 1px solid #666;
        padding-bottom: 3px;
    }

    .label {
        font-weight: bold;
        width: 200px;
        display: inline-block;
        vertical-align: top;
    }

    .description {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #fff;
        min-height: 60px;
    }

    .qr-block {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        margin-bottom: 25px;
    }

    .qr-block img {
        width: 120px;
        height: 120px;
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 4px;
        margin-right: 20px;
    }

    .cert-info {
        font-size: 11pt;
        line-height: 1.4;
    }

    .footer {
        font-size: 9pt;
        text-align: center;
        color: #666;
        margin-top: 30px;
    }
</style>

<div class="header">FEER</div>
<div class="subheader">Karta konsultacyjna</div>

<!-- QR + dane użytkownika -->
<div class="qr-block">
    @if(!empty($qrImage))
        <img src="data:image/png;base64,{{ $qrImage }}" alt="QR Code">
    @endif
    <div class="cert-info">
        <div><strong>Użytkownik:</strong> {{ Auth::user()->name }}</div>
        <div><strong>CN certyfikatu:</strong> {{ $certificate['CN'] ?? $certificate['common_name'] ?? '-' }}</div>
        <div><strong>Ważny od-do:</strong> {{ $certificate['valid_from'] ?? '-' }} – {{ $certificate['valid_to'] ?? '-' }}</div>
        <div><strong>Certyfikat serwera:</strong> {{ $serverCertificate['CN'] ?? $serverCertificate['common_name'] ?? '-' }} ({{ $serverCertificate['valid_from'] ?? '-' }} – {{ $serverCertificate['valid_to'] ?? '-' }})</div>
        @if(isset($certificate['is_test_certificate']) && $certificate['is_test_certificate'])
            <div style="color:red;"><strong>Certyfikat testowy</strong></div>
        @endif
    </div>
</div>

<!-- Sekcja 1: Informacje o kliencie i konsultacji -->
<div class="section">
    <div class="section-title">Informacje o kliencie i konsultacji</div>
    <div><span class="label">Klient:</span> {{ $consultation->client->name ?? '-' }}</div>
    <div><span class="label">Data i godzina:</span> {{ \Carbon\Carbon::parse($consultation->consultation_datetime)->format('d.m.Y H:i') ?? '-' }}</div>
    <div><span class="label">Czas trwania:</span> {{ $consultation->duration_minutes ?? '-' }} min</div>
    <div><span class="label">Status:</span> {{ $status ?? '-' }}</div>
    <div><span class="label">Przeprowadzono przez:</span> {{ $conductedBy ?? '-' }}</div>
</div>

<!-- Sekcja 2: Opis konsultacji -->
<div class="section">
    <div class="section-title">Opis konsultacji</div>
    <div class="description">{{ $consultation->description ?? '-' }}</div>
</div>

<!-- Sekcja 3: Podpis cyfrowy -->
<div class="section">
    <div class="section-title">Podpis cyfrowy i dane autoryzacyjne</div>
    <div><span class="label">Zatwierdził:</span> {{ $approvedBy ?? '-' }}</div>
    <div><span class="label">IP użytkownika:</span> {{ $ipFormatted ?? '-' }} <small>({{ $ipRaw ?? '-' }})</small></div>
    <div><span class="label">Data wydruku:</span> {{ $printDateTime ?? '-' }}</div>
    <div><span class="label">Miesiąc sprawozdawczy:</span> {{ $reportMonth ?? '-' }}</div>
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

<div class="footer">
    Dokument wewnętrzny do sprawozdawczości | RODO: zgoda | PDF wygenerowany automatycznie | FEER
</div>

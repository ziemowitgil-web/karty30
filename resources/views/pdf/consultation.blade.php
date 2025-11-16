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
        font-size: 20pt;
        font-weight: bold;
        color: #003366;
        margin-bottom: 10px;
        border-bottom: 3px solid #003366;
        padding-bottom: 5px;
    }

    .subheader {
        font-size: 16pt;
        font-weight: bold;
        margin-bottom: 25px;
        text-align: center;
    }

    .section {
        margin-bottom: 25px;
        padding: 20px;
        border: 1px solid #444;
        border-radius: 6px;
        background-color: #f9f9f9;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 12px;
        font-size: 14pt;
        border-bottom: 1px solid #666;
        padding-bottom: 5px;
    }

    .label {
        font-weight: bold;
        width: 220px;
        display: inline-block;
        vertical-align: top;
        color: #003366;
    }

    .value {
        display: inline-block;
        vertical-align: top;
    }

    .description {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #fff;
        min-height: 60px;
        line-height: 1.4;
    }

    .qr-block {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 15px;
    }

    .qr-block img {
        width: 130px;
        height: 130px;
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 6px;
        margin-right: 25px;
    }

    .cert-info {
        font-size: 11pt;
        line-height: 1.5;
    }

    .footer {
        font-size: 10pt;
        text-align: center;
        color: #666;
        margin-top: 35px;
        border-top: 1px solid #ccc;
        padding-top: 10px;
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
        <div><span class="label">Użytkownik:</span> <span class="value">{{ Auth::user()->name }}</span></div>
        <div><span class="label">CN certyfikatu:</span> <span class="value">{{ $certificate['CN'] ?? $certificate['common_name'] ?? '-' }}</span></div>
        <div><span class="label">Ważny od-do:</span> <span class="value">{{ $certificate['valid_from'] ?? '-' }} – {{ $certificate['valid_to'] ?? '-' }}</span></div>
        <div><span class="label">Certyfikat serwera:</span>
            <span class="value">{{ $serverCertificate['CN'] ?? $serverCertificate['common_name'] ?? '-' }} ({{ $serverCertificate['valid_from'] ?? '-' }} – {{ $serverCertificate['valid_to'] ?? '-' }})</span>
        </div>
        @if(isset($certificate['is_test_certificate']) && $certificate['is_test_certificate'])
            <div style="color:red;"><strong>Certyfikat testowy</strong></div>
        @endif
    </div>
</div>

<!-- Sekcja 1: Informacje o kliencie i konsultacji -->
<div class="section">
    <div class="section-title">Informacje o kliencie i konsultacji</div>
    <div><span class="label">Klient:</span> <span class="value">{{ $consultation->client->name ?? '-' }}</span></div>
    <div><span class="label">Data i godzina:</span> <span class="value">{{ \Carbon\Carbon::parse($consultation->consultation_datetime)->format('d.m.Y H:i') ?? '-' }}</span></div>
    <div><span class="label">Czas trwania:</span> <span class="value">{{ $consultation->duration_minutes ?? '-' }} min</span></div>
     
</div>

<!-- Sekcja 2: Opis konsultacji -->
<div class="section">
    <div class="section-title">Opis konsultacji</div>
    <div class="description">{{ $consultation->description ?? '-' }}</div>
</div>

<!-- Sekcja 3: Podpis cyfrowy -->
<div class="section">
    <div class="section-title">Podpis cyfrowy i dane autoryzacyjne</div>
    <div><span class="label">Data wydruku:</span> <span class="value">{{ $printDateTime ?? '-' }}</span></div>
    <div><span class="label">Common Name (CN):</span> <span class="value">{{ $certificate['CN'] ?? $certificate['common_name'] ?? '-' }}</span></div>
    <div><span class="label">E-mail:</span> <span class="value">{{ $certificate['email'] ?? '-' }}</span></div>
    <div><span class="label">Organizacja (O):</span> <span class="value">{{ $certificate['O'] ?? $certificate['organization'] ?? '-' }}</span></div>
    <div><span class="label">Jednostka organizacyjna (OU):</span> <span class="value">{{ $certificate['OU'] ?? $certificate['organizational_unit'] ?? '-' }}</span></div>
    <div><span class="label">Data ważności od:</span> <span class="value">{{ $certificate['valid_from'] ?? '-' }}</span></div>
    <div><span class="label">Data ważności do:</span> <span class="value">{{ $certificate['valid_to'] ?? '-' }}</span></div>
    <div><span class="label">SHA1 certyfikatu:</span> <span class="value">{{ $certificate['sha1'] ?? '-' }}</span></div>
    @if(isset($certificate['is_test_certificate']) && $certificate['is_test_certificate'])
        <div style="color:red;"><strong>Certyfikat testowy</strong></div>
    @endif
</div>

<div class="footer">
    Dokument wewnętrzny do sprawozdawczości | RODO: zgoda | PDF wygenerowany automatycznie | FEER
</div>

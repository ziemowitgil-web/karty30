<!-- Górny blok: QR + dane użytkownika i certyfikatów -->
<div style="display: flex; justify-content: flex-start; align-items: flex-start; margin-bottom: 25px; border-bottom: 2px solid #003366; padding-bottom: 10px;">

    <!-- QR Code -->
    @if(!empty($qrImage))
        <div style="margin-right: 20px;">
            <img src="data:image/png;base64,{{ $qrImage }}" alt="QR Code" style="width:120px; height:120px; border:1px solid #ccc; padding:5px; border-radius:4px;">
        </div>
    @endif

    <!-- Dane użytkownika i certyfikatów -->
    <div style="font-size: 11pt; line-height:1.4;">
        <div><strong>Użytkownik:</strong> {{ Auth::user()->name }}</div>
        <div><strong>CN certyfikatu:</strong> {{ $certificate['CN'] ?? $certificate['common_name'] ?? '-' }}</div>
        <div><strong>Data ważności certyfikatu:</strong> {{ $certificate['valid_from'] ?? '-' }} – {{ $certificate['valid_to'] ?? '-' }}</div>
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

<!-- Sekcja 3: Podpis cyfrowy i dane autoryzacyjne -->
<div class="section">
    <div class="section-title">Podpis cyfrowy</div>
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

<!-- QR kod i dane użytkownika -->
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <!-- QR Code -->
    @if(!empty($qrImage))
        <div class="qr-code">
            <img src="data:image/png;base64,{{ $qrImage }}" alt="QR Code">
        </div>
    @endif

    <!-- Dane użytkownika i certyfikatów -->
    <div style="font-size: 10pt; line-height: 1.3; max-width: 300px;">
        <div><strong>Użytkownik:</strong> {{ Auth::user()->name }}</div>
        <div><strong>CN certyfikatu:</strong> {{ $certificate['CN'] ?? $certificate['common_name'] ?? '-' }}</div>
        <div><strong>Data ważności certyfikatu:</strong> {{ $certificate['valid_from'] ?? '-' }} – {{ $certificate['valid_to'] ?? '-' }}</div>
        <div><strong>Certyfikat serwera:</strong> {{ $serverCertificate['CN'] ?? $serverCertificate['common_name'] ?? '-' }} ({{ $serverCertificate['valid_from'] ?? '-' }} – {{ $serverCertificate['valid_to'] ?? '-' }})</div>
        @if(isset($certificate['is_test_certificate']) && $certificate['is_test_certificate'])
            <div style="color:red;"><strong>Certyfikat testowy</strong></div>
        @endif
    </div>
</div>

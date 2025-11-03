<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Karta uczestnika - {{ $client->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.3; color: #000; margin: 20px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 14pt; }
        .header .stamp { font-size: 10pt; font-style: italic; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td { padding: 4px; vertical-align: top; }
        .label { width: 30%; font-weight: bold; }
        .value { width: 70%; }

        .clause { border: 1px solid #000; padding: 8px; background: #f5f5f5; font-size: 10pt; max-height: 280px; overflow: hidden; }
        .clause p, .clause ol { margin: 3px 0; }
        .signatures { margin-top: 25px; display: flex; justify-content: space-between; }
        .sign-box { width: 45%; text-align: center; }
        .sign-line { margin-top: 50px; border-top: 1px solid #000; }
        .meta { font-size: 9pt; margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h2>Karta uczestnika Fundacji Edukacji Empatii Rozwoju (FEER)</h2>
    <div class="stamp">Barbackiego 28/18, 33-300 Nowy Sącz</div>
</div>

<div class="meta">
    Data i godzina wydruku: {{ now()->format('d.m.Y H:i') }}<br>
    Wydruk: {{ Auth::user()->name }}
</div>

<table>
    <tr>
        <td class="label">Imię i nazwisko:</td>
        <td class="value">{{ $client->name }}</td>
    </tr>
    <tr>
        <td class="label">Email:</td>
        <td class="value">{{ $client->email ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Telefon:</td>
        <td class="value">{{ $client->phone ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Status:</td>
        <td class="value">
            @php
                $statusMap = [
                    'enrolled' => 'Zapisany',
                    'ready' => 'Gotowy',
                    'to_settle' => 'Do rozliczenia',
                    'other' => 'Inne'
                ];
            @endphp
            {{ $statusMap[$client->status] ?? $client->status }}
        </td>
    </tr>
    <tr>
        <td class="label">Problem:</td>
        <td class="value">{{ $client->problem ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Sprzęt:</td>
        <td class="value">{{ $client->equipment ?? '-' }}</td>
    </tr>
</table>

<div class="clause">
    <strong>Klauzula RODO i oświadczenie o wyrażeniu zgody:</strong>
    <ol>
        <li>Administratorem danych osobowych moich oraz małoletniego, w tym zarejestrowanego wizerunku w ramach działań statutowych
            Administratora - jest Fundacja Edukacji Empatii Rozwoju FEER z siedzibą w Nowym Sączu przy ul. Barbackiego 28/18, KRS: 0000779281, NIP: 7343570539;</li>
        <li>Dane osobowe moje oraz małoletniego przetwarzane będą na podstawie zgody wyrażonej powyżej w związku z dobrowolnym
            zgłoszeniem chęci udziału małoletniego w działaniach statutowych Fundacji;</li>
        <li>Moje dane osobowe oraz małoletniego, w szczególności wizerunek, mogą zostać udostępnione podmiotom współpracującym z
            Administratorem w celu realizacji szkolenia, bądź instytucjom uprawnionym do kontroli działalności Administratora lub do
            uzyskania danych osobowych na podstawie odrębnych przepisów prawa;</li>
        <li>Moje dane osobowe oraz małoletniego będą przetwarzane przez Fundację przez okres wyrażonej zgody;</li>
        <li>Posiadam prawo dostępu do danych, ich sprostowania, usunięcia, ograniczenia przetwarzania, prawo do cofnięcia zgody, oraz prawo do przenoszenia danych. Kontakt: kontakt@feer.org.pl;</li>
        <li>Mam prawo wniesienia skargi do Prezesa Urzędu Ochrony Danych Osobowych;</li>
        <li>Podanie danych jest dobrowolne, lecz konieczne dla uczestnictwa i dokumentacji działań Fundacji;</li>
        <li>Dane nie będą podlegać profilowaniu ani przekazywane poza UE.</li>
    </ol>
    <p>
        <strong>Oświadczenie o wyrażeniu zgody:</strong><br>
        @if($client->consent)
            Wyrażono zgodę na przetwarzanie danych osobowych.
        @else
            Brak wyrażonej zgody.
        @endif
    </p>
</div>

<div class="signatures">
    <div class="sign-box">
        Pracownik Fundacji
        <div class="sign-line"></div>
    </div>
    <div class="sign-box">
        Uczestnik / Rodzic
        <div class="sign-line"></div>
    </div>
</div>
</body>
</html>

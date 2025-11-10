<?php
@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Szczegóły certyfikatu</h1>

        @if(!$certData)
            <div class="bg-red-100 text-red-700 p-4 rounded">
                Brak certyfikatu dla użytkownika.
            </div>
        @else
            <div class="bg-white shadow rounded p-6">
                <table class="w-full table-auto">
                    <tr>
                        <td class="font-semibold py-2">Common Name (CN)</td>
                        <td>{{ $certData['common_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold py-2">E-mail</td>
                        <td>{{ $certData['email'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold py-2">Organization (O)</td>
                        <td>{{ $certData['organization'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold py-2">Organizational Unit (OU)</td>
                        <td>{{ $certData['organizational_unit'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold py-2">Ważny od</td>
                        <td>{{ $certData['valid_from'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold py-2">Ważny do</td>
                        <td>{{ $certData['valid_to'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold py-2">SHA1</td>
                        <td>{{ $certData['sha1'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold py-2">Certyfikat testowy</td>
                        <td>
                            @if($isTestCert)
                                <span class="text-yellow-700 font-bold">TAK (staging)</span>
                            @else
                                NIE
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endif
    </div>
@endsection



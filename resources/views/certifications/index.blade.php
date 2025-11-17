<?php
@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-xl">
        <h1 class="text-2xl font-bold mb-6">Panel certyfikatów X.509</h1>

        <div class="space-y-4">
            <div class="p-4 bg-white rounded shadow flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-lg">Generuj nowy certyfikat</h2>
                    <p class="text-gray-600 text-sm">Utwórz self-signed certyfikat dla swojego konta.</p>
                </div>
                <a href="{{ route('certificates.generate') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Przejdź
                </a>
            </div>

            <div class="p-4 bg-white rounded shadow flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-lg">Podgląd certyfikatu</h2>
                    <p class="text-gray-600 text-sm">Zobacz szczegóły i pobierz certyfikat lub klucz prywatny.</p>
                </div>
                <a href="{{ route('certificates.details') }}"
                   class="bg-green-600 text-white px-4 py-2 rounded hover:bg-

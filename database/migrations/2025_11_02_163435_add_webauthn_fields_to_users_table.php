<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Uruchamia migracje.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Pola WebAuthn
            $table->string('webauthn_credential_id')->nullable()->unique();
            $table->binary('webauthn_public_key')->nullable();
            $table->unsignedBigInteger('webauthn_counter')->default(0);

            // Pola dotyczące dokumentów uprawniających
            $table->string('document_number')->nullable()->after('webauthn_counter');
            $table->string('document_issuer')->nullable()->after('document_number');
            $table->string('document_type')->nullable()->after('document_issuer');
        });
    }

    /**
     * Cofanie migracji.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'webauthn_credential_id',
                'webauthn_public_key',
                'webauthn_counter',
                'document_number',
                'document_issuer',
                'document_type',
            ]);
        });
    }
};

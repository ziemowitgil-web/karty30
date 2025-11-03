<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->boolean('confirmed')->default(false)->after('status');
            $table->string('next_action')->nullable()->after('confirmed');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn('confirmed');
            $table->dropColumn('next_action');
        });
    }
};

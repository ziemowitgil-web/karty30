<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('used')->default(0); // pole wykorzystane
            $table->json('available_hours')->nullable()->after('time_slots'); // dostępne godziny w formacie JSON
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['used', 'available_hours']);
        });
    }
};
?>


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $lastYear = DB::table('projects')->max('year');
        $now = now();
        DB::table('settings')->insert([
            // Fermé par défaut : l'admin ouvre la saisie depuis l'interface.
            ['key' => 'phase', 'value' => 'closed', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'current_year', 'value' => (string) ($lastYear ?? (date('n') >= 8 ? date('Y') + 1 : date('Y'))), 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

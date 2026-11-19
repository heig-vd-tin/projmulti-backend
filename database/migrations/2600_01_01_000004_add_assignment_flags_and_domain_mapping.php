<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - users.excluded : étudiant retiré de la répartition (sujet personnel, abandon).
 * - users.locked   : affectation figée, la répartition automatique ne la change pas.
 * - domain_orientation : quelles orientations couvrent un domaine (de base / en élargissant),
 *   auparavant codé en dur par id dans AssignmentController.
 * - noms des domaines en français.
 */
return new class extends Migration
{
    /** Correspondance validée par l'utilisateur le 19.11.2026 (ids des orientations). */
    private const MAPPING = [
        1 => ['base' => [7, 8], 'enlarged' => []],      // Mécanique : MI, SIC
        2 => ['base' => [2], 'enlarged' => [7, 3]],     // Électronique : EEM (+ MI, EN)
        3 => ['base' => [2, 3], 'enlarged' => [7]],     // Électricité : EEM, EN (+ MI)
        4 => ['base' => [5, 6], 'enlarged' => []],      // Thermique : THI, THO
        5 => ['base' => [4], 'enlarged' => []],         // Énergie : EBA
        6 => ['base' => [1], 'enlarged' => [2, 7]],     // Programmation : EAI (+ EEM, MI)
    ];

    private const DOMAIN_NAMES = [
        1 => ['Mecanic', 'Mécanique'],
        2 => ['Electronic', 'Électronique'],
        3 => ['Electricity', 'Électricité'],
        4 => ['Thermic', 'Thermique'],
        5 => ['Energy', 'Énergie'],
        6 => ['Programming', 'Programmation'],
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('excluded')->default(false)->after('orientation_id');
            $table->boolean('locked')->default(false)->after('excluded');
        });

        Schema::create('domain_orientation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('domain_id');
            $table->unsignedBigInteger('orientation_id');
            $table->boolean('enlarged')->default(false);
            $table->unique(['domain_id', 'orientation_id']);
            $table->foreign('domain_id')->references('id')->on('domains')->cascadeOnDelete();
            $table->foreign('orientation_id')->references('id')->on('orientations')->cascadeOnDelete();
        });

        $existingDomains = DB::table('domains')->pluck('id')->all();
        $existingOrientations = DB::table('orientations')->pluck('id')->all();
        foreach (self::MAPPING as $domain => $lists) {
            foreach (['base' => false, 'enlarged' => true] as $key => $enlarged) {
                foreach ($lists[$key] as $orientation) {
                    if (in_array($domain, $existingDomains) && in_array($orientation, $existingOrientations)) {
                        DB::table('domain_orientation')->insert([
                            'domain_id' => $domain, 'orientation_id' => $orientation, 'enlarged' => $enlarged,
                        ]);
                    }
                }
            }
        }

        foreach (self::DOMAIN_NAMES as $id => [$en, $fr]) {
            DB::table('domains')->where('id', $id)->where('name', $en)->update(['name' => $fr]);
        }
    }

    public function down(): void
    {
        foreach (self::DOMAIN_NAMES as $id => [$en, $fr]) {
            DB::table('domains')->where('id', $id)->where('name', $fr)->update(['name' => $en]);
        }
        Schema::dropIfExists('domain_orientation');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['excluded', 'locked']);
        });
    }
};

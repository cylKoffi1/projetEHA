<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mot_de_passe_utilisateur', function (Blueprint $table) {
            $table->string('niveau_acces_id')->nullable();
            $table->foreign('niveau_acces_id')->references('id')->on('niveau_acces_donnees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mot_de_passe_utilisateur', function (Blueprint $table) {
            $table->dropForeign(['niveau_acces_id']);
            $table->dropColumn('niveau_acces_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fichiers', function (Blueprint $table) {
            // Passe owner_id en VARCHAR (191 pour compat MySQL)
            $table->string('owner_id', 191)->change();
        });
    }

    public function down(): void
    {
        Schema::table('fichiers', function (Blueprint $table) {
            // Revenir en int si besoin (ajuste selon ton schéma d’origine)
            $table->unsignedBigInteger('owner_id')->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupeUtilisateurHasPermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('groupe_utilisateur_has_permissions', function (Blueprint $table) {
            $table->string('groupe_utilisateur_id', 50);
            $table->unsignedBigInteger('permission_id');

            $table->foreign('groupe_utilisateur_id')->references('code')->on('groupe_utilisateur')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->primary(['groupe_utilisateur_id', 'permission_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('groupe_utilisateur_has_permissions');
    }
}


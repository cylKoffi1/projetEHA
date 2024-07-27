<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('reattributions', function (Blueprint $table) {
            $table->id();
            $table->string('code_projet');
            $table->string('code_agence')->nullable();
            $table->string('code_chef')->nullable();
            $table->date('changement');
            $table->string('type_reattribution');
            $table->json('motifs')->nullable();
            $table->text('motif')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reattributions');
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRenforcementBeneficiaireTable extends Migration
{
    public function up()
    {
        Schema::create('renforcement_beneficiaire', function (Blueprint $table) {
            $table->string('renforcement_capacite');
            $table->string('code_acteur');

            $table->foreign('renforcement_capacite')
                ->references('code_renforcement')->on('renforcement_capacites')
                ->onDelete('cascade');

            $table->foreign('code_acteur')
                ->references('code_acteur')->on('acteur')
                ->onDelete('cascade');

            $table->primary(['renforcement_capacite', 'code_acteur']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('renforcement_beneficiaire');
    }
}

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRenforcementProjetTable extends Migration
{
    public function up()
    {
        Schema::create('renforcement_projet', function (Blueprint $table) {
            $table->string('renforcement_capacite');
            $table->string('code_projet');

            $table->foreign('renforcement_capacite')
                ->references('code_renforcement')->on('renforcement_capacites')
                ->onDelete('cascade');

            $table->foreign('code_projet')
                ->references('code_projet')->on('projets')
                ->onDelete('cascade');

            $table->primary(['renforcement_capacite', 'code_projet']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('renforcement_projet');
    }
}

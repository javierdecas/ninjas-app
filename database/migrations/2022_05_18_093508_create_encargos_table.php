<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEncargosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('encargos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('descripcion');
            $table->integer('numero_ninjas_necesarios');
            $table->enum('estado', ['pendiente', 'en curso', 'completado', 'fallado']);
            $table->date('fecha_finalizacion')->nullable();
            $table->string('pago');
            $table->enum('prioridad', ['normal', 'urgente']);
            $table->foreignId('cliente_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('encargos');
    }
}

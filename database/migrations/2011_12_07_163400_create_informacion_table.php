<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('informacion', function (Blueprint $table) {
            $table->id();
            $table->string('direccion', 80)->nullable();
            $table->string('calle', 80)->nullable();
            $table->string('calle dos', 80)->nullable();
            $table->string('numero_casa', 10)->nullable();
            $table->string('numero_casa_telefono', 16)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('informacion');
    }
};

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
        Schema::create('transacaos', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id')->unsigned();
        $table->string('nome_produto');
        $table->string('valor_produto', 20);
        $table->integer('ordem_id')->unsigned();
        $table->string('forma_pagamento');
        $table->string('status', 20);
        $table->string('tipo_cartao')->nullable();
        $table->date('data_pagamento');
        $table->dateTime('data_vencimento');
        $table->string('parcelas', 10)->nullable();
        $table->string('valor_parcela', 20)->nullable();
        $table->bigInteger('user_id')->unsigned();
        $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacaos');
    }
};

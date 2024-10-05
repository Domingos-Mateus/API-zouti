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
        Schema::create('ordens', function (Blueprint $table) {
            $table->id();
            $table->string('nome_produto');
            $table->string('valor_produto');
            $table->string('total_pedidos');
            $table->string('quantidade_pedidos_pix');
            $table->string('percentagem_conversao_pix');
            $table->string('quantidade_pedidos_cartao');
            $table->string('genero_cliente');
            $table->bigInteger('user_id')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens');
    }
};

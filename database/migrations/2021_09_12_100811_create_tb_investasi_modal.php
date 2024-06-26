<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbInvestasiModal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_investasi_modal', function (Blueprint $table) {
            // Columns
            $table->integer('id')->autoIncrement();
            $table->date('tgl_transaksi');
            $table->integer('id_apotek');
            $table->integer('id_investor');
            $table->string('saham');
            $table->double('jumlah_modal');
            $table->double('persentase_kepemilikan');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->boolean('is_deleted')->default('0');

            // Foreign Key Constraints
            $table->foreign('id_apotek')
                ->references('id')
                ->on('tb_m_apotek');
            $table->foreign('id_investor')
                ->references('id')
                ->on('tb_m_investor');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
            $table->foreign('updated_by')
                ->references('id')
                ->on('users');
            $table->foreign('deleted_by')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_investasi_modal');
    }
}

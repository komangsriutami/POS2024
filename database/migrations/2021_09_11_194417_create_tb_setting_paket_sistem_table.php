<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbSettingPaketSistemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_setting_paket_sistem', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_jenis_paket_sistem');
            $table->integer('fee');
            $table->double('jumlah_user');
            $table->double('jumlah_dokter');
            $table->string('keterangan');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->boolean('is_deleted');
            
            //Foreign Key
            $table->foreign('id_jenis_paket_sistem')
                ->references('id')
                ->on('tb_m_jenis_paket_sistem');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_setting_paket_sistem');
    }
}

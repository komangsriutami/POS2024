<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbMInvestor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_m_investor', function (Blueprint $table) {
            // Columns
            $table->integer('id')->autoIncrement();
            $table->char('nik', '16');
            $table->string('nama');
            $table->date('tgl_lahir');
            $table->string('tempat_lahir');
            $table->integer('id_jenis_kelamin') ;
            $table->integer('id_agama');
            $table->integer('id_kewarganegaraan');
            $table->string('no_telp');
            $table->string('email');
            $table->string('npwp', '15');
            $table->text('alamat');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->boolean('is_deleted')->default('0');

            // Foreign Key Constraints
            $table->foreign('id_jenis_kelamin')
                ->references('id')
                ->on('tb_m_jenis_kelamin');
            $table->foreign('id_agama')
                ->references('id')
                ->on('tb_m_agama');
            $table->foreign('id_kewarganegaraan')
                ->references('id')
                ->on('tb_m_kewarganegaraan');
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
        Schema::dropIfExists('tb_m_investor');
    }
}

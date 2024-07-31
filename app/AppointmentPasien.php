<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Validator;
use Auth;

class AppointmentPasien extends Model
{
    use SoftDeletes;

    protected $table = 'tb_appointment_pasien';
    public $primaryKey = 'id';
    protected $fillable = [
        'id_reg_pasien', 
        'id_jadwal', 
        'id_dokter', 
        'no_ticket', 
        'no_urut',
        'jam_kedatangan', 
        'keluhan', 
        'alergi', 
        'rating',
        'status',
        'no_rm',
    ];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_dokter' => 'required|max:255',
            'id_reg_pasien' => 'required', 
            'id_jadwal' => 'required', 
            'no_ticket' => 'required|max:255', 
            'no_urut' => 'required|max:255',
            'jam_kedatangan' => 'required|max:255', 
            'keluhan'=> 'required', 
        ]);
    }
}

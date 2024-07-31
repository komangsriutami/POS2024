<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterSpesialis extends Model
{
    /*
		Model 	: Model spesialis untuk Dokter@Controller
		Author 	: Tangkas.
		Date 	: 16/06/2021
	*/

    protected $table = 'tb_m_spesialis';
    public $primaryKey = 'id';
    protected $fillable = [
        'spesialis',
        'keterangan'
    ];
}

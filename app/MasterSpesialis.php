<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DynamicConnectionTrait;

class MasterSpesialis extends Model
{
    use DynamicConnectionTrait;
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

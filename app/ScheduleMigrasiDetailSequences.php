<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleMigrasiDetailSequences extends Model
{
    use HasFactory;

    protected $table = 'm_schedule_migrasi_detail_sequence';

    public function migrasi_detail(){
        return $this->hasOne('App\ScheduleMigrasiDetail', 'id', 'id_migrasi_detail');
    }
}

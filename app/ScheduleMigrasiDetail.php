<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleMigrasiDetail extends Model
{
    use HasFactory;

    protected $table = 'm_schedule_migrasi_detail';

    public function migrasi(){
        return $this->hasOne('App\ScheduleMigrasi', 'id', 'id_migrasi');
    }
}

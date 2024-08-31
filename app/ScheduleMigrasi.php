<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleMigrasi extends Model
{
    use HasFactory;

    protected $table = 'm_schedule_migrasi';

    public function detail_migrasi(){
        return $this->hasMany('App\ScheduleMigrasiDetail', 'id_migrasi', 'id');
    }

}

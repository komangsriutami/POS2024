<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DynamicConnectionTrait;

class Appointment extends Model
{
    //
    use DynamicConnectionTrait;
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DynamicConnectionTrait;

class RekamMedis extends Model
{
    use DynamicConnectionTrait;
}

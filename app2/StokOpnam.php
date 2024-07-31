<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
use App\Traits\DynamicConnectionTrait;

class StokOpnam extends Model
{
    //
    use DynamicConnectionTrait;
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use App\Traits\DynamicConnectionTrait;

class Tips extends Model
{
    use DynamicConnectionTrait;
    /*
		Model 	: Untuk News
		Author 	: Sri U.
		Date 	: 16/06/2021
	*/
    protected $table = 'tb_tips';
    public $primaryKey = 'id';
    protected $fillable = ['title', 'content', 'image', 'img', 'slug'];

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'title' => 'required|max:255',
            'content' => 'required',
            'img' => 'required',
            'image' => 'required',
            'slug' => 'required',
        ]);
    }

    public function save_plus()
    {
        $this->created_by = Auth::user()->id;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save_edit()
    {
        $this->updated_by = Auth::user()->id;
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function created_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}

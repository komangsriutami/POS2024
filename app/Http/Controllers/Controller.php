<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getConnection() {
        $conn = 'mysql';
        if(session('id_tahun_active') != "" OR !is_null(session('id_tahun_active'))) {
            $tahun = session('id_tahun_active');
            if (date('Y') != $tahun) {
                $conn = 'db_' . $tahun;
            } 
        }
        return $conn;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DynamicConnectionTrait;

class HomeDKController extends Controller
{
    use DynamicConnectionTrait;
    public function __construct()
    {
        $this->middleware('auth:dokter');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home_dokter');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\TesDataTable;
use App\DataTables\TesDataTableEditor;
use App\Traits\DynamicConnectionTrait;

class TesController extends Controller
{
    use DynamicConnectionTrait;
    public function index(TesDataTable $dataTable)
    {
        return $dataTable->render('users.index');
    }

    public function store(TesDataTableEditor $editor)
    {
        return $editor->process(request());
    }
}

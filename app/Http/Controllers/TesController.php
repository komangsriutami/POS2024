<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\TesDataTable;
use App\DataTables\TesDataTableEditor;
class TesController extends Controller
{
    public function index(TesDataTable $dataTable)
    {
        return $dataTable->render('users.index');
    }

    public function store(TesDataTableEditor $editor)
    {
        return $editor->process(request());
    }
}

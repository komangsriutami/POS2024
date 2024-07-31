<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests;
use App\MasterApotek;
use App\DataTables\ObatDataTable;
use App\DataTables\ObatDataTableEditor;
use App\SettingStokOpnam;
use App\User;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EditObatController extends Controller
{
    public function index(ObatDataTable $dataTable) {
        return $dataTable->render('data_obat.edit_data_obat');
    }

    public function store(ObatDataTableEditor $editor)
    {
        return $editor->process(request());
    }
}

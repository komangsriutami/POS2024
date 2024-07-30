<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\News;
use Illuminate\Support\Str;
use Auth;
use App;
use Datatables;
use DB;
use File;
use App\Traits\DynamicConnectionTrait;

class NewsController extends Controller
{
    use DynamicConnectionTrait;
    # untuk menampilkan halaman awal
    public function index()
    {
        return view('news.index');
    }

    # untuk menampilkan mengambil data dari database
    public function list_news(Request $request)
    {
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = News::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_news.*'])
            ->where(function ($query) use ($request) {
                $query->where('tb_news.is_deleted', '=', '0');
            });

        $datatables = Datatables::of($data);
        return $datatables
            ->filter(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orwhere('title', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('content', 'LIKE', '%' . $request->get('search')['value'] . '%');
                });
            })
            ->editcolumn('content', function($data) {
                $text = strip_tags($data->content);
                $panjang = strlen($text);

                return substr($text, 0, 100).($panjang>100 ? '...' : '');
            })
            ->addColumn('image', function ($data) {
	            if (($data->image == "#") || (empty($data->image))) {
	                return '-';
	            } else {
	                return '<div class="col-md-4"><img src="data:image/png;base64,'.$data->image.'" width="200"></div>';
	            }
	        })
            ->addcolumn('action', function ($data) {
                $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="show_data(' . $data->id . ')" data-toggle="tooltip" data-placement="top" title="Show Data"><i class="fa fa-eye"></i></span>';
                $btn .= '<a class="btn btn-success" href="'.url('news/'.$data->id.'/edit').'" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></a>';
                $btn .= '<span class="btn btn-danger" onClick="delete_data(' . $data->id . ')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['action', 'title', 'content', 'image'])
            ->addIndexColumn()
            ->make(true);
    }

    # untuk menampilkan form create
    public function create()
    {
        $data_ = new News; //inisialisasi array
        $data_->setDynamicConnection();

        return view('news.create')->with(compact('data_'));
    }

    # untuk menyimpan data dari form store
    public function store(Request $request)
    {
        $data_ = new News;
        $data_->setDynamicConnection();
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        # file image | tambahan sri 14/6/2021
        $date_now = date('Y-m-d H:i:s');
        if (!empty($request->img)) {
            $nama_cover = $request->img->getClientOriginalName();
            $split_co = explode('.', $nama_cover);
            $ext_co = $split_co[1];
            $file_name_co = md5($split_co[0] . "-" . $date_now);
            $logo = $request->img;
            $destination_path_co = public_path("temp/");
            $destination_filename_co = $file_name_co . "." . $ext_co;
            $data_->img->move($destination_path_co, $destination_filename_co);
            $data_->img = $file_name_co . "." . $ext_co;
            
            # SRI | convert image to blob
            $path = $destination_path_co.$destination_filename_co;
            $logo = file_get_contents($path);
            $base64 = base64_encode($logo);
            $data_->image = $base64;

            # SRI | hapus image
            if (File::exists($path)) {
                unlink($path);
            }
        }
        $data_->slug = Str::slug($data_->title, '-');
        $data_->created_by = Auth::user()->id;
        $data_->created_at = date('Y-m-d H:i:s');

        $validator = $data_->validate();
        if ($validator->fails()) {
            Session()->flash('error', 'Gagal menyimpan data!');
            return view('news.create')->with(compact('data_'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                Session()->flash('success', 'Sukses menyimpan data!');
                return redirect('/news');
            } else {
                Session()->flash('error', 'Gagal menyimpan data!');
                return redirect('/news');
            }
        }
    }

    # untuk menampilakn data detail show
    public function show($id)
    {
        $data_ = News::on($this->getConnectionName())->find($id);

        return view('news.show')->with(compact('data_'));
    }

    # untuk menampilkan form edit
    public function edit($id)
    {
        $data_ = News::on($this->getConnectionName())->find($id);
        return view('news.edit')->with(compact('data_'));
    }

    # untuk menyimpan data edit
    public function update(Request $request, $id)
    {
        $data_ = News::on($this->getConnectionName())->find($id);
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        # file image | tambahan sri 14/6/2021
        $date_now = date('Y-m-d H:i:s');
        if ($request->hasFile('img')) {
            $image = $request->file('img');
            $nama_cover = $image->getClientOriginalName();
            $split_co = explode('.', $nama_cover);
            $ext_co = $split_co[1];
            $file_name_co = md5($split_co[0] . "-" . $date_now);
            $logo = $request->img;
            $destination_path_co = public_path("temp/");
            $destination_filename_co = $file_name_co . "." . $ext_co;
            $data_->img->move($destination_path_co, $destination_filename_co);
            $data_->img = $file_name_co . "." . $ext_co;

            # SRI | convert image to blob
            $path = $destination_path_co.$destination_filename_co;
            $logo = file_get_contents($path);
            $base64 = base64_encode($logo);
            $data_->image = $base64;

            # SRI | hapus image
            if (File::exists($path)) {
                unlink($path);
            }
        } else {
            $data_->img = $data_->img;
        }
        
        $data_->slug = Str::slug($data_->title, '-');
        $data_->updated_by = Auth::user()->id;
        $data_->updated_at = date('Y-m-d H:i:s');

        $validator = $data_->validate();
        if ($validator->fails()) {
            Session()->flash('error', 'Gagal menyimpan data!');
            return view('news.edit')->with(compact('data_'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                Session()->flash('success', 'Sukses menyimpan data!');
                return redirect('/news');
            } else {
                Session()->flash('error', 'Gagal menyimpan data!');
                return redirect('/news');
            }
        }
    }

    # untuk menghapus data
    public function destroy($id)
    {
        $data_ = News::on($this->getConnectionName())->find($id);
        $data_->is_deleted = 1;
        $data_->deleted_at = date('Y-m-d H:i:s');
        $data_->deleted_by = Auth::user()->id;
        if($data_->save()){
            echo 1;
        } else {
            echo 0;
        }
    }
}

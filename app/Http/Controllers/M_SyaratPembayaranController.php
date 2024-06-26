<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use View;

use App\MasterSyaratPembayaran;

class M_SyaratPembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $syarat_pembayaran = new MasterSyaratPembayaran;
        $method = "POST";
        $route = ['syaratpembayaran.store'];

        if(isset($request->Fromjson)){
            
            $fromjson = $request->Fromjson;
            $status = 1;
            $form = View::make('syarat_pembayaran.modalform',compact('syarat_pembayaran','method','route','fromjson'))->render();
            return json_encode(compact('status','form'));

        } else {
            return view('syarat_pembayaran.create')->with(compact('syarat_pembayaran','method','route'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->input());
        $syarat_pembayaran = new MasterSyaratPembayaran;
        $syarat_pembayaran->fill($request->except('_token'));

        $validator = $syarat_pembayaran->validate();

        if(isset($request->fromjson)){
            if($validator->fails()){
                $status = array("status" => 2);
            } else {
                $syarat_pembayaran->save();
                $option = '<option data-waktu="'.$syarat_pembayaran->jangka_waktu.'" value="'.$syarat_pembayaran->id.'">'.$syarat_pembayaran->nama.'</option>';
                $status = array("status" => 1, "option" => $option, "id" => $syarat_pembayaran->id);
            }

            echo json_encode($status);

        } else {
            if($validator->fails()){
                return view('syarat_pembayaran.create')->with(compact('syarat_pembayaran'))->withErrors($validator);
            }else{
                $syarat_pembayaran->created_by = Auth::user()->id;
                $syarat_pembayaran->created_at = date('Y-m-d H:i:s');
                $syarat_pembayaran->save();
                session()->flash('success', 'Sukses menyimpan data!');
                return redirect('syaratpembayaran');
            }
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

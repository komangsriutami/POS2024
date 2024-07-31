<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\RbacMenu;
use App\Icon;
use App\RbacPermission;
use Datatables;
use DB;
use URL;

class MenuController extends Controller
{
    /*
    	=================================================================================================================
    	For     : Tampilan index menu
    	Author 	: 
		Date 	: 
		=================================================================================================================
    */
    public function index()
    {
        $menus = RbacMenu::where('is_deleted', 0)->pluck('nama_panjang','id');
        $menus->prepend('-- Pilih Menu --','');

        return view('menu.index')->with(compact('menus'));
    }


    /*
        =================================================================================================================
        For     : Get List menu
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function list_menu(Request $request)
    {
        # ini untuk mencari menu yang menjadi parent
        $parent = RbacMenu::where('is_deleted', 0)
            ->where( function($query){
                $query->where('parent', 0);
                $query->orwhere('parent', null);
            })
            ->orderby('weight','asc')->get();

        if($parent != null){
            $menu = '<ol class="sortable">';

            foreach ($parent as $p) {
                #untuk mencari sub parent menu
                $sub_menu = RbacMenu::where('is_deleted', 0)->where('parent', $p->id)->where('sub_parent', 0)->orderby('weight','asc')->get();                
                $menu .= $this->build_menu($p);

                if($sub_menu != null){
                    $menu .= '<ol class="sortable">';
                    foreach ($sub_menu as $s) {
                        #untuk mencari menu
                        $mn = RbacMenu::where('is_deleted', 0)->where('sub_parent', $s->id)->orderby('weight','asc')->get();    
                        $menu .= $this->build_menu($s);

                        if($mn != null){
                            $menu .= '<ol class="sortable">';
                            foreach ($mn as $m) {
                                $menu .= $this->build_menu($m);
                                $menu .= '</li>';
                            }
                            $menu .= '</ol>';
                        }
                        $menu .= '</li>';
                    }
                    $menu .= '</ol>';
                }
                $menu .= '</li>';
            }
            $menu .= '</ol>';
        }
    
        return $menu;
    }


    /*
        =================================================================================================================
        For     : build element menu
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function build_menu($m)
    {
        $locale = "nama_singkatan";
        $namamenu = $m->$locale;

        $list = '<li id="list_'.$m->id.'"><div><span class="disclose"><span></span></span><i style="color:#3B464A;" class="'.$m->icon.' fa-fw"></i> '.$namamenu.' | link : '.$m->link.' <a a href="#" onclick="delete_menu('.$m->id.')" style="display: inline-block;float: right;width: auto;cursor: pointer;z-index:9999;"><i class="fa fa-fw fa-trash-o"></i> Hapus</a><a href="'.url("/menu/".$m->id."/edit/").'" style="display: inline-block;float: right;width: auto;margin-right: 17px;cursor: pointer;z-index:999;"><i class="fa fa-fw fa-edit"></i> Edit</a></div>';

       return $list;
    }


    /*
        =================================================================================================================
        For     : update sorting drag menu
        Author  : 
        Date    : 
        =================================================================================================================
    */

    public function update_sorting_menu(Request $request)
    {
        $list = $request->input('list');
        $weight = 0;
        foreach ($list as $id => $value) {
            $weight++;

            $menu = RbacMenu::find($id);

            if($value != 'null'){
                $menu->parent = $value;
            } else {
                $menu->parent = 0;
            }

            $menu->weight = $weight;
            $menu->save();
        } 
        
        echo true;
    }







    /*
    	=================================================================================================================
    	For     : call create menu function
    	Author 	: 
		Date 	: 
		=================================================================================================================
    */
    public function create()
    {
        $menu = new RbacMenu;

        $parents = RbacMenu::where('is_deleted', 0)->where('parent', 0)->get();

        $sub_parents = RbacMenu::where('is_deleted', 0)->where('parent', '!=', 0)->get();

        $icons = Icon::all();

        return view('menu.create')->with(compact('menu', 'sub_parents', 'parents', 'icons'));
    }




    /*
    	=================================================================================================================
    	For     : store data menu
    	Author 	: 
		Date 	: 
		=================================================================================================================
    */
    public function store(Request $request)
    {
        $menu = new RbacMenu;
        $menu->fill($request->except('_token'));

        $parents = RbacMenu::where('is_deleted', 0)->where('parent', 0)->get();

        $sub_parents = RbacMenu::where('is_deleted', 0)->where('parent', '!=', 0)->get();

        $icons = Icon::all();

        if($request->parent == "") {
            $menu->parent = 0;
        }

        if($request->sub_parent== "") {
            $menu->sub_parent = 0;
        }

        $validator = $menu->validate();
        if($validator->fails()){
            return view('menu.create')->with(compact('menu', 'sub_parents', 'parents', 'icons'))->withErrors($validator);
        }else{
            $menu->save_now(1);
            session()->flash('success', 'Data berhasil disimpan!');
            return redirect('menu');
        }
    }

    /*
    	=================================================================================================================
    	For     : Tidak digunakan
    	Author 	: 
		Date 	: 
		=================================================================================================================
    */
    public function show($id)
    {

    }



    /*
    	=================================================================================================================
    	For     : call edit menu function
    	Author 	: 
		Date 	: 
		=================================================================================================================
    */
    public function edit($id)
    {
        $menu = RbacMenu::find($id);

        $parents = RbacMenu::where('is_deleted', 0)->where('parent', 0)->get();

        $sub_parents = RbacMenu::where('is_deleted', 0)->where('parent', '!=', 0)->get();

        $icons = Icon::all();

        return view('menu.edit')->with(compact('menu', 'parents', 'sub_parents', 'icons'));
    }



    /*
    	=================================================================================================================
    	For     : update data menu
    	Author 	: 
		Date 	: 
		=================================================================================================================
    */
    public function update(Request $request, $id)
    {
        $menu = RbacMenu::find($id);
        $menu->fill($request->except('_token'));
        $menu->sub_parent = 0;

        $parents = RbacMenu::where('is_deleted', 0)->where('parent', 0)->get();

        $sub_parents = RbacMenu::where('is_deleted', 0)->where('parent', '!=', 0)->get();

        $icons = Icon::all();

        $validator = $menu->validate();
        if($validator->fails()){
            return view('menu.edit')->with(compact('menu', 'sub_parents', 'parents', 'icons'))->withErrors($validator);
        }else{
            $menu->save_now(2);
            session()->flash('success', trans('Data berhasil diperbaharui!'));
            return redirect('menu')->with('message', 'Data berhasil diperbaharui!');
        }
    }



    /*
    	=================================================================================================================
    	For     : delete data menu
    	Author 	: 
		Date 	: 
		=================================================================================================================
    */
    public function destroy($id)
    {
        $menu = RbacMenu::find($id);
        $menu->is_deleted = 1;

        if($menu->save()){
            echo 1;
        }else{
            echo 0;
        }
      
    }
}

@extends('layout.app')

@section('title')
Absensi User
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Absensi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Absensi User</li>
</ol>
@endsection

@section('content')
{!! Form::model(new App\Absensi, ['route' => ['absensi.store'], 'class'=>'validated_form']) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div id="box-pencarian" class="box-body panel-collapse" aria-expanded="true">
                        <form role="form">
                            <!-- text input -->
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Pilih User</label>
                                    {!! Form::select('text_cari', $users, null, ['id' => 'text_cari', 'class' => 'form-control required input_select']) !!}
                                </div>
                                <div class="col-lg-12" style="text-align: center;">
                                    <button type="button" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>  
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="hasil"></div>
                    <br>
                    <div class="box box-success" id="main-box" style="">
                        {!! Form::model(new App\Absensi, ['route' => ['absensi.store'], 'class'=>'validated_form']) !!}
                        <div class="box-body">
                            <div class="form-group col-md-12">
                                <table width="100%">
                                    <tr>
                                        <td width="27%">User</td>
                                        <td width="2%"> : </td>
                                        <td width="70"><span id="nama_user">--User belum dipilih--</span></td>
                                        {!! Form::hidden('id_user', null, array('id' => 'id_user', 'class' => 'form-control', 'placeholder'=>'ID User', 'readonly' => 'readonly')) !!}    
                                    </tr>
                                    <tr>
                                        <td width="27%">Status</td>
                                        <td width="2%"> : </td>
                                        <td width="70"><span id="status">--User belum dipilih--</span></td>
                                        {!! Form::hidden('id_status', null, array('id' => 'id_status', 'class' => 'form-control', 'placeholder'=>'Status', 'readonly' => 'readonly')) !!}
                                    </tr>
                                </table>
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('password', 'Masukan Password') !!}
                                {!! Form::password('password', null, array('id' => 'password', 'class' => 'form-control', 'placeholder'=>'Masukan Password')) !!}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group action-group">
                                    <button class="btn btn-primary" id="btn_masuk" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Absen Masuk</button> 
                                    <button class="btn btn-success" id="btn_keluar" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Absen Pulang</button> 
                                    <button class="btn btn-primary" id="btn_masuk_split" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Absen Masuk Split</button> 
                                    <button class="btn btn-success" id="btn_keluar_split" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Absen Pulang Split</button> 
                                    <a href="{{url('/absensi')}}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                                </div>  
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('script')
    @include('absensi/_form_js')
@endsection


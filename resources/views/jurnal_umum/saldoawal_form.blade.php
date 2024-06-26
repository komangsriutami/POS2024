<div class="card">
  <div class="card-header">
    <h3 class="card-title center">Saldo Awal Akun Kategori <b>{{$kategori->nama}}</b></h3>
  </div>
  <div class="card-body"  style="height: 600px; overflow-y: scroll;">
    <div class="row">
      <div class="col-sm-12">
        @if($kode_akun->count())
          <table width="100%" class="table table-bordered table-striped table-hover table-head-fixed text-nowrap mb-0">
            <thead>
              <tr>
                <th width="20%">Kode Akun</th>
                <th width="50%">Nama Akun</th>
                <th width="30%">Saldo Awal</th>
              </tr>
            </thead>
            <tbody>
              @foreach($kode_akun as $a)
                <tr>
                  <td>{{$a->kode}}</td>
                  <td>{{$a->nama}}</td>
                  <?php if(isset($detail[$a->id])){ $saldoawal = $detail[$a->id]; } else {$saldoawal = '';} ?>
                  <td><input class="form-control text-right" type="number" name="saldoawal[{{$a->id}}]" value="{{$saldoawal}}" required></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @else
          <span class="text-muted"><i class="fa fa-warning"></i> Data Akun kosong</span>
        @endif
      </div>
    </div>
  </div>
  <!-- /.card-body -->
  <div class="border-top">
    <div class="card-body">
      <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
    </div>
  </div>
</div>
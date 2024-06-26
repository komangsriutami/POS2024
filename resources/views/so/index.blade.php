@extends('layout.app')

@section('title')
Home
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item active" aria-current="page">Home</li>
</ol>
@endsection

@section('content')
    <div class="card mb-12 border-left-primary card-info">
        <div class="card-body">
            <div class="row">
                <?php 
                    $nama_apotek_panjang_active = session('nama_apotek_panjang_active');
                    $id_apotek_active = session('id_apotek_active');
                    $date = date('d-m-Y H:i:s');
                ?>
                @if(empty($id_apotek_active))
                    <div class="col-md-12">
                        <br>
                        <p class="text-red"><cite><b>Anda belum memilih apotek, silakan pilih apotek terlebih dahulu!</b></cite></p>
                    </div>
                @else
                    <div class="container">
                        {{$dataTable->table(['id' => 'users'])}}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
<script type="text/javascript">
    var token = '{{csrf_token()}}';
    $(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{csrf_token()}}'
                    }
                });

                var editor = new $.fn.dataTable.Editor({
                    ajax: "{{ url('/tes') }}",
                    table: "#users",
                    display: "bootstrap",
                    fields: [
                        {label: "Name:", name: "nama"},
                        {label: "Email:", name: "email"},
                        {label: "Password:", name: "password", type: "password"}
                    ]
                });

                $('#users').on('click', 'tbody td:not(:first-child)', function (e) {
                    alert("dasdas");
                    editor.inline(this);
                });

                {{$dataTable->generateScripts()}}
            })
    $(document).ready(function(){
        
    })

</script>
@endsection

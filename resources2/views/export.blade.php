@include('/layout/header')
<div id="loader"></div>
@include('/layout/footer') 
<script type="text/javascript">
	var token = '{{csrf_token()}}';

    $(document).ready(function(){
        var spinner = $('#loader');
        spinner.show();
        
        $('body').append('<div id="load_view" hidden></div>');
        $('#load_view').append('<table id="tb_apotek" hidden></table>');
        var tb_apotek = $('#tb_apotek').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                url: '{{url("apotek/list_apotek")}}',
                data:function(d){
                    d.length = -1
                }
            },
            columns: [
                {data: 'no', name: 'no'},
                {data: 'nama_panjang', name: 'nama_panjang'}
            ],
            initComplete: async function(settings, json) {
                let last_loop = json.data.length - 1;
                for(let i = 0; i < json.data.length; i++){
                    console.log(json.data[i].id);
                    $('#load_view').append(`<table id="tb_penjualan_apotek_${i}" hidden></table>`);
                    var tb_penjualan_apotek = $(`#tb_penjualan_apotek_${i}`).dataTable( {
                        paging : false,
                        processing: true,
                        serverSide: true,
                        stateSave: true,
                        ajax:{
                            url: '{{url("home/list_pareto_penjualan")}}',
                            data:function(d){
                                d.id_pencarian = @json($id_pencarian);
                                d.tanggal = @json($tanggal);
                                d.id_apotek = json.data[i].id;
                            }
                        },
                        columns: [
                            {data: 'no', name: 'no',width:"2%"},
                            {data: 'nama', name: 'nama'},
                            {data: 'id_penandaan_obat', name: 'id_penandaan_obat'},
                            {data: 'id_satuan', name: 'id_satuan'},
                            {data: 'id_produsen', name: 'id_produsen'},
                            {data: 'jumlah_penjualan', name: 'jumlah_penjualan', class:'text-center'},
                            {data: 'penjualan', name: 'penjualan', class:'text-right'},
                            {data: 'persentase_penjualan', name: 'persentase_penjualan', class:'text-center'},
                            { 
                                data: 'klasifikasi_penjualan', 
                                name: 'klasifikasi_penjualan', 
                                class: 'text-center',
                                render: function (data, type, row) {
                                    if (data === 1) {
                                        return 'A';
                                    } else if (data === 2) {
                                        return 'B';
                                    } else if (data === 3) {
                                        return 'C';
                                    } else {
                                        return '';
                                    }
                                }
                            },
                            {data: 'keuntungan', name: 'keuntungan', class:'text-right'},
                            {data: 'persentase_keuntungan', name: 'persentase_keuntungan', class:'text-center'},
                            { 
                                data: 'klasifikasi_keuntungan', 
                                name: 'klasifikasi_keuntungan', 
                                class: 'text-center',
                                render: function (data, type, row) {
                                    if (data === 1) {
                                        return 'A';
                                    } else if (data === 2) {
                                        return 'B';
                                    } else if (data === 3) {
                                        return 'C';
                                    } else {
                                        return '';
                                    }
                                }
                            },
                            { 
                                data: 'klasifikasi_pareto', 
                                name: 'klasifikasi_pareto', 
                                class: 'text-center',
                                render: function (data, type, row) {
                                    if (data === 1) {
                                        return 'A';
                                    } else if (data === 2) {
                                        return 'B';
                                    } else if (data === 3) {
                                        return 'C';
                                    } else {
                                        return '';
                                    }
                                }
                            },
                            {data: 'stok_akhir', name: 'stok_akhir', class:'text-right'}
                        ],
                        columnDefs: [
                            {
                                targets: [0],
                                orderData: [12, 8, 11, 13],
                            },
                            {
                                targets: [2],
                                orderData: [2, 12, 8, 11, 13]
                            },
                            {
                                targets: [3],
                                orderData: [3, 12, 8, 11, 13]
                            },
                            {
                                targets: [4],
                                orderData: [4, 12, 8, 11, 13]
                            },
                            {
                                targets: [5],
                                orderData: [5, 12, 8, 11, 13]
                            },
                            {
                                targets: [8],
                                orderData: [8, 11, 12, 13]
                            },
                            {
                                targets: [11],
                                orderData: [11, 8, 12, 13]
                            },
                            {
                                targets: [12],
                                orderData: [12, 8, 11, 13]
                            },
                            {
                                targets: [13],
                                orderData: [13, 12, 8, 11, 13]
                            }
                        ],
                        order: [[0, 'desc']],
                        rowCallback: function( row, data, iDisplayIndex ) {
                        },
                        drawCallback: function( settings ) {
                            var api = this.api();
                        },
                        initComplete: function(settings, json) {
                            console.log( json );
                            if(i == last_loop){
                                if (i === last_loop) {
                                    const url = "{{ url('home/export_pareto_all') }}" + "?id_pencarian=" + @json($id_pencarian) + "&tanggal=" + @json($tanggal);
                                    window.open(url, "_blank");
                                    spinner.hide();
                                    window.close();
                                }
                            };
                        }
                    });
                }
            },
        });
    })
</script>
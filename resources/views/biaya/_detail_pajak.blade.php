@if(!is_null($akun_pajak))
    @foreach($akun_pajak as $key => $p)
        @if($list_biaya[$p->id] != 0 && $list_biaya[$p->id] != "")
            <?php if($p->is_pemotongan) {
                $color = "text-red";
                $pajak = "(".number_format($subtotalpajak_perakun[$p->id]).")";
            } else {
                $color = "";
                $pajak = number_format($subtotalpajak_perakun[$p->id]);
            } ?>
            <div class="col-sm-4 text-right"></div>
            <div class="col-sm-4 text-right {{$color}}"><i>{{$p->nama.' '.$p->persentase_efektif.'%'}}</i></div>
            <div class="col-sm-4 text-right">
                <table width="100%" border="0" class="{{$color}}">
                    <tr><td width="25%" align="right">Rp.</td><td align="right" width="75%">{{$pajak}}</td></tr>
                </table>
            </div>
        @endif
    @endforeach
        <?php /*print_r(implode('<br>',$history_hitung_pajak));*/ ?>
@endif
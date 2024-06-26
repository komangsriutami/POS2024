<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Cetak Nota Penjualan</title>
<style type="text/css">
    @media print {
            .hidden-print,
            .hidden-print * {
                display: none !important;
            }
        }

    .btn-sm {
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
        }

        .btn-info {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
            box-shadow: none;
        }
        .btn {
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
            transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }

</style>
{!! Html::script('assets/plugins/jquery/jquery.min.js') !!}
<script type="text/javascript">
function closePrint () {
  document.body.removeChild(this.__container__);
}

function setPrint () {
  this.contentWindow.__container__ = this;
  this.contentWindow.onbeforeunload = closePrint;
  this.contentWindow.onafterprint = closePrint;
  this.contentWindow.focus(); // Required for IE
  this.contentWindow.print();
}

function printNota (id) {
  var sURL = "{{ url('/penjualan/load_page_print_nota/') }}/"+id;
  var oHideFrame = document.createElement("iframe");
  oHideFrame.onload = setPrint;
  oHideFrame.style.position = "fixed";
  oHideFrame.style.right = "0";
  oHideFrame.style.bottom = "0";
  oHideFrame.style.width = "0";
  oHideFrame.style.height = "0";
  oHideFrame.style.border = "0";
  oHideFrame.src = sURL;
  document.body.appendChild(oHideFrame);
}

$(document).ready(function(){
    var url = window.location.href;
    var id = url.substring(url.lastIndexOf('/') + 1);
    printNota(id);

    $(document).on("keyup", function(e){
        
        var x = e.keyCode || e.which;
        if (x == 16) {  
            // fungsi shift 
        } else if(x==113){
            printNota(id);
        }
    })
})

</script>
</head>

<body>
    <input type="hidden" name="id" value="{{ $penjualan->id }}">
    @if($penjualan->is_kredit == 1)
    <a href="{{ url('/penjualan/create_credit') }}" class="hidden-print btn btn-sm btn-info" style="text-decoration:none;margin:0;color: #fff;background-color: #dc3545;border-color: #dc3545;box-shadow: none; font-size:10pt;">Back | Shift</a>
    @else
    <a  href="{{ url('/penjualan/create') }}" class="hidden-print btn btn-sm btn-info" style="text-decoration:none;margin:0;color: #fff;background-color: #dc3545;border-color: #dc3545;box-shadow: none; font-size:10pt;">Back | Shift</a>
    @endif
    <p><span onclick="printNota({{$penjualan->id}});" class="hidden-print btn btn-sm btn-info" style="margin:0;color: #fff;background-color: #17a2b8;border-color: #17a2b8;box-shadow: none; font-size:10pt;">Print Nota |F2</span></p>

</body>
</html>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Cetak Closing Kasir</title>
<style type="text/css">
    @media print {
            .hidden-print,
            .hidden-print * {
                display: none !important;
            }
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

function printPage (id) {
    var sURL = "{{ url('/penjualan/load_page_print_closing_kasir/') }}/"+id;
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
    printPage(id);

    $(document).on("keyup", function(e){
        
        var x = e.keyCode || e.which;
        if (x == 16) {  
            // fungsi shift 
        } else if(x==113){
            printPage(id);
        }
    })
})

</script>
</head>

<body>
    <input type="hidden" name="id" value="{{ $data->id }}">
    <p><span onclick="printPage({{$data->id}});" style="cursor:pointer;text-decoration:underline;color:#0000ff;">Print Nota |F2</span></p>
</body>
</html>
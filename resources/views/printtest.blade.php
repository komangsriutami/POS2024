<!DOCTYPE html>
<html>
<head>
    <title>Cetak</title>
</head>
<body>
    <button onclick="cetak()">Cetak</button>
    
    <script>
        function cetak() {
            var teks = "Ini adalah teks yang akan dicetak.";
            var cetakWindow = window.open('', '', 'width=600,height=600');
            cetakWindow.document.open();
            cetakWindow.document.write('<html><head><title>Cetak</title></head><body>');
            cetakWindow.document.write(teks);
            cetakWindow.document.write('</body></html>');
            cetakWindow.document.close();
            cetakWindow.print();
            //cetakWindow.close();
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A3 portrait;
            margin: 10mm;
        }

        .grid-container {
            width: 100%;
            /* overflow: hidden; <-- Hapus ini, kadang bikin masalah di PDF */
        }

        .grid-item {
            float: left;
            /* KUNCI PERBAIKAN: Gunakan mm agar ukuran terkunci */
            /* 277mm area kerja / 5 kolom = 55.4mm. Kita pakai 54mm biar aman */
            width: 53mm; 
            padding: 5px;
        }

        .card-container {
            background-color: #7b52ab;
            color: white;
            text-align: center;
            height: 290px; 
            padding: 10px 10px;
            position: relative;
        }

        .card-title {
            font-size: 26pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            line-height: 1;
        }

        .card-subtitle {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
            padding: 0 5px;
            line-height: 1.2;
        }

        .qr-wrapper {
            background-color: white;
            display: inline-block;
            width: 100%;
        }

        .qr-wrapper img {
            width: 100%;
            height: auto;
        }

        .card-footer {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
        }

        .page-break {
            clear: both;
            page-break-after: always;
        }
        
        
        /* Tambahan helper untuk menutup float terakhir */
        .clearfix {
            clear: both;
            content: "";
            display: block;
        }
    </style>
</head>
<body>

    <div class="grid-container">

        @foreach($qrCodes as $item)
            <div class="grid-item">
                <div class="card-container">
                    <div class="card-title">NEXA</div>
                    <div class="card-subtitle">SCAN ME TO CHECK FIRE EXTINGUISHER</div>

                    <div class="qr-wrapper" style="margin-top: 30px; margin-left: 5px">
                        {{-- Pastikan path image benar --}}
                        <img src="{{ public_path('storage/' . $item['path']) }}" alt="QR">
                    </div>

                    <div class="card-footer" style="margin-top: 5px;">TAN ANUGRAH SEJAHTERA</div>
                </div>
            </div>

            {{-- LOGIC BARIS BARU: Setiap 5 item, lakukan clear float (kecuali jika itu halaman baru) --}}
            @if(($loop->iteration % 5) == 0)
                <div style="clear: both;"></div> 
            @endif

            {{-- LOGIC HALAMAN BARU: Setiap 15 item (3 baris x 5 kolom) --}}
            @if(($loop->iteration % 20) == 0)
                <div class="page-break"></div>
            @endif

        @endforeach
        

        
        {{-- Penutup container float yang aman --}}
        <div class="clearfix"></div>

    </div>

</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        @page {
            size: A3 portrait;
            /* Margin kertas dipersempit maksimal */
            margin: 5mm;
        }

        .grid-container {
            width: 100%;
            display: block;
            /* Menghilangkan whitespace antar elemen inline-block */
            font-size: 0;
            line-height: 0;
        }

        .grid-item {
            display: inline-block;
            vertical-align: top;
            /* Ukuran tetap 53mm */
            width: 53mm;
            /* Jarak antar stiker dibuat sangat tipis (0.5mm) */
            margin: 0.5mm;
            padding: 0;
            font-size: 0; /* Menghilangkan sisa gap font */
        }

        .card-container {
            /* Jika ingin benar-benar mepet, hilangkan border ini atau ubah jadi warna sangat tipis */
            border: 0.1mm solid #ddd;
            background-color: #ffffff;
            text-align: center;
            /* Tinggi disamakan dengan lebar agar presisi kotak */
            height: 53mm;
            padding: 0;
            position: relative;
            overflow: hidden;
        }

        .qr-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2mm; /* Ruang sedikit agar QR tidak menyentuh garis potong */
        }

        .qr-wrapper img {
            /* QR akan mengisi penuh container dikurangi padding 2mm */
            width: 49mm;
            height: 49mm;
            display: block;
        }

        /* Overlay teks agar tidak memakan space vertical */
        .card-title {
            position: absolute;
            top: 1mm;
            left: 0;
            width: 100%;
            font-size: 8pt;
            font-weight: bold;
            line-height: 1;
            z-index: 10;
            background: rgba(255,255,255,0.7); /* Transparan agar tidak menutupi QR */
        }

        .card-footer {
            position: absolute;
            bottom: 1mm;
            left: 0;
            width: 100%;
            font-size: 7pt;
            line-height: 1;
            z-index: 10;
        }

        .page-break {
            clear: both;
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="grid-container">
        @foreach($qrCodes as $item)
            <div class="grid-item">
                <div class="card-container">
                    {{-- Teks dibuat absolute/overlay agar tidak mendorong QR ke bawah --}}
                    @if(!empty($item['title']))
                        <div class="card-title">{{ $item['title'] }}</div>
                    @endif

                    <div class="qr-wrapper">
                        <img src="{{ public_path('storage/' . $item['path']) }}" alt="QR">
                    </div>

                    @if(!empty($item['footer']))
                        <div class="card-footer">{{ $item['footer'] }}</div>
                    @endif
                </div>
            </div>

            {{--
                MAX OPTIMIZATION (A3):
                5 kolom x 7 baris = 35 stiker per halaman.
            --}}
            @if($loop->iteration % 35 == 0)
                <div class="page-break"></div>
            @endif
        @endforeach
    </div>

</body>
</html>

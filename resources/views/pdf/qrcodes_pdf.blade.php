<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Mengatur halaman untuk cetak A3 */
        @page {
            size: A3 portrait;
            margin: 10mm;
        }

        /* Styling tabel utama - menjaga layout agar aman untuk PDF generator */
        .qr-table {
            width: 100%;
            border-collapse: separate; /* Gunakan separate agar border-spacing berfungsi */
            border-spacing: 10px; /* Jarak antar kartu */
        }

        /* Styling sel tabel */
        .qr-table td {
            width: 20%; /* SAYA UBAH JADI 5 KOLOM (20%) AGAR KARTU TIDAK TERLALU GEPENG */
            vertical-align: top;
            padding: 0;
        }

        /* ---- DESAIN KARTU NEXA ---- */
        .card-container {
            background-color: #7b52ab; /* Warna Ungu (sesuaikan kode hex jika perlu) */
            color: white;
            padding: 15px 10px;
            text-align: center;
            border-radius: 0px; /* Siku tajam sesuai gambar */
        }

        .card-title {
            font-size: 28pt; /* Ukuran besar untuk 'NEXA' */
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            line-height: 1;
        }

        .card-subtitle {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        .qr-wrapper {
            background-color: white;
            padding: 5px; /* Memberikan border putih di sekitar QR */
            display: inline-block;
            margin-bottom: 10px;
        }

        .qr-wrapper img {
            width: 100%;
            height: auto;
            display: block;
        }

        .card-footer {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        /* Kode unik item (optional: agar tahu ini QR untuk item mana) */
        .item-code {
            font-size: 7pt;
            margin-top: 2px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <table class="qr-table">
        <tr>
            @foreach($qrCodes as $item)
            <td class="qr-cell">
                <div class="card-container">
                    <div class="card-title">NEXA</div>
                    
                    <div class="card-subtitle">SCAN ME TO CHECK FIRE EXTINGUISHER</div>
                    
                    <div class="qr-wrapper">
                        <img src="{{ public_path('storage/' . $item['path']) }}" alt="QR">
                    </div>

                    <div class="card-footer">TAN ANUGRAH SEJAHTERA</div>
                </div>
            </td>

            {{-- Logic Loop: Ganti baris setiap 5 item (karena width 20%) --}}
            @if(($loop->index + 1) % 5 == 0)
        </tr><tr>
            @endif
            @endforeach
        </tr>
    </table>
</body>
</html>
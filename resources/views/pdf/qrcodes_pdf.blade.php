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
            size: A3 portrait; /* Mengatur ukuran kertas menjadi A3 potret */
            margin: 10mm; /* Menambahkan margin 1 cm di setiap sisi */
        }

        /* Styling tabel utama */
        .qr-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        /* Styling sel tabel */
        .qr-table td {
            width: 16.66%; /* Contoh: 6 kolom dalam satu baris (100% / 6 = 16.66%) */
            padding: 5mm; /* Jarak antar sel */
            text-align: center;
            vertical-align: top;
        }
        
        /* Styling gambar QR */
        .qr-item img {
            width: 100%;
            max-width: 80mm; /* Contoh: Lebar maksimal gambar sekitar 8cm */
            height: auto;
            display: block;
        }

        .qr-item p {
            font-size: 12pt;
            margin-top: 5px;
            word-wrap: break-word; /* Memastikan teks panjang pindah baris */
        }
    </style>
</head>
<body>
    <table class="qr-table">
        <tr>
            @foreach($qrCodes as $item)
            <td class="qr-cell">
                <div class="qr-item">
                    <img src="{{ public_path('storage/' . $item['path']) }}" alt="QR {{ $item['barcode'] }}">
                    <p>{{ $item['barcode'] }}</p>
                </div>
            </td>
            @if(($loop->index + 1) % 6 == 0)
        </tr><tr>
            @endif
            @endforeach
        </tr>
    </table>
</body>
</html>
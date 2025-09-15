<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inspeksi APAR</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <div class="header-section">
         @if ($kop && $kop->image)
            <p>{{ public_path($kop->image) }}</p>
            <img src="{{ public_path("storage/" . $kop->image) }}" width="100%" alt="">
        @endif
    </div>
    <h2>Laporan Inspeksi APAR</h2>
    <p><strong>No Jadwal:</strong> {{ $agenda->no_jadwal }}</p>
    <p><strong>Inspeksi:</strong> {{ $agenda->inspeksi_title }}</p>
    <p><strong>PIC:</strong> {{ $agenda->inspection_name }}</p>
    <p><strong>Jumlah APAR:</strong> {{ $agenda->jumlah_apar }}</p>
    <p><strong>Tanggal Mulai:</strong> {{ \Carbon\Carbon::parse($agenda->tgl_mulai)->translatedFormat('d F Y') }}</p>

    <h3>Detail APAR</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Barcode</th>
                <th>Media</th>
                <th>Kapasitas</th>
                <th>Status</th>
                <th>QC</th>
                <th>tanggal_cek</th>
                <th>lokasi</th>
                <th>pressure</th>
                <th>selang</th>
                <th>head valve</th>
                <th>Korosi</th>
                <th>expired</th>
            </tr>
        </thead>
        <tbody>
            @foreach($apar as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->media }}</td>
                    <td>{{ $item->kapasitas }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->qc_name }}</td>
                    <td>{{ $item->tanggal_cek }}</td>
                    <td>{{ $item->lokasi ?? '-' }}</td>
                    <td>
                        {{ $item->detail_pressure }} 
                    </td>
                    <td>{{ $item->detail_hose }}</td>
                    <td>{{ $item->detail_head_valve }}</td>
                    <td>{{ $item->detail_korosi }}</td>
                    <td>{{ $item->detail_expired }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

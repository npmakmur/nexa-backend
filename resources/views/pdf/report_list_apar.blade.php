<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan APAR</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <div class="header-section">
         @if ($kop && $kop->image)
            <img src="{{ public_path("storage/" . $kop->image) }}" width="100%" alt="">
        @endif
    </div>
    <h2>Laporan APAR</h2>
    {{-- <p><strong>No Jadwal:</strong> {{ $agenda->no_jadwal }}</p>
    <p><strong>Inspeksi:</strong> {{ $agenda->inspeksi_title }}</p>
    <p><strong>PIC:</strong> {{ $agenda->inspection_name }}</p>
    <p><strong>Jumlah APAR:</strong> {{ $agenda->jumlah_apar }}</p>
    <p><strong>Tanggal Mulai:</strong> {{ \Carbon\Carbon::parse($agenda->tgl_mulai)->translatedFormat('d F Y') }}</p> --}}

    <h3>Detail APAR</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode Barang</th>
                <th>Media</th>
                <th>Kapasitas</th>
                <th>Status</th>
                <th>tanggal terakhir pengecekan</th>
                <th>lokasi</th>
                <th>Titik penempatan</th>
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
                    <td>
                        <img src="{{ url('storage/qrcodes/' . $item->kode_barang . '.png') }}" alt="{{ $item->kode_barang }}" width="50">
                    </td>
                    <td>{{ $item->media }}</td>
                    <td>{{ $item->kapasitas }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->last_inspection }}</td>
                    <td>{{ $item->lokasi ?? '-' }}</td>
                    <td>{{ $item->titik_penempatan_id ?? '-' }}</td>
                    <td>
                        {{ $item->pressure ?? '-' }} 
                    </td>
                    <td>{{ $item->hose ?? '-'}}</td>
                    <td>{{ $item->head_valve?? '-' }}</td>
                    <td>{{ $item->korosi ?? '-'}}</td>
                    <td>{{ $item->expired ?? '-'}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

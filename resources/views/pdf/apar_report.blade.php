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
    {{-- <div class="header-section">
         @if ($kop && $kop->image)
            <img src="{{ public_path("storage/" . $kop->image) }}" width="100%" alt="">
        @endif
    </div> --}}
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
                    <td>
                        {{ $item->detail_hose }}
                        <br>
                        @if($item->hose_img)
                            <img src="{{ asset("storage/" . $item->hose_img) }}" alt="" width="100%">
                        @endif
                    </td>
                    <td>
                        {{ $item->detail_head_valve }}
                        <br>
                        @if($item->head_valve_img)
                            <img src="{{ asset("storage/" . $item->head_valve_img) }}" alt="" width="100%">
                        @endif
                    </td>
                    <td>
                        {{ $item->detail_korosi }}
                        <br>
                        @if($item->korosi_img)
                            <img src="{{ asset("storage/" . $item->korosi_img) }}" alt="" width="100%">
                        @endif
                    </td>
                    <td>
                        {{ $item->detail_expired }}
                        <br>
                        @if($item->expired_img)
                            <img src="{{ asset("storage/" . $item->expired_img) }}" alt="" width="100%">
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

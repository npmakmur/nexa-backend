<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 6px; text-align: left; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="title">LAPORAN PENAWARAN PERBAIKAN PART APAR</div>

    <p><strong>No Jadwal:</strong> {{ $data->no_jadwal }}</p>
    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y') }}</p>
    <p><strong>PIC Inspeksi:</strong> {{ $data->inspection_name }}</p>
    <p><strong>Dibuat oleh:</strong> {{ $data->created_name }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Part</th>
                <th>Kondisi</th>
                <th>Qty</th>
                <th>Harga Satuan (Rp)</th>
                <th>Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list_penawaran as $i => $item)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $item['part'] }}</td>
                <td>{{ $item['kondisi'] }}</td>
                <td>{{ $item['qty'] }}</td>
                <td>{{ number_format($item['harga'], 0, ',', '.') }}</td>
                <td>{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align:right;">TOTAL</th>
                <th>{{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>

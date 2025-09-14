<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.5;
        }
        .header-section {
            margin-bottom: 20px;
        }
        .title { 
            text-align: center; 
            font-size: 18px; 
            font-weight: bold; 
            margin-bottom: 5px;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }
        .info {
            font-size: 12px;
            margin-bottom: 5px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        table, th, td { 
            border: 1px solid #ddd; 
        }
        th, td { 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
            color: #333;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tfoot th {
            text-align: right;
            background-color: #e0e0e0;
            font-size: 14px;
        }
        .total-cell {
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header-section">

       <img src="../public/surat/header.png" alt="hai">
    </div>

    <div class="info-section">
        <p class="info"><strong>No Jadwal:</strong> {{ $data->no_jadwal }}</p>
        <p class="info"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y') }}</p>
        <p class="info"><strong>PIC Inspeksi:</strong> {{ $data->inspection_name }}</p>
        <p class="info"><strong>Dibuat oleh:</strong> {{ $data->created_name }}</p>
    </div>
    
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
                <th colspan="5">TOTAL</th>
                <th class="total-cell">{{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
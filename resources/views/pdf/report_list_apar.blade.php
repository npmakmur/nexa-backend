<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan APAR</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #334155;
            font-size: 11px;
            line-height: 1.4;
            background-color: #ffffff;
        }
        
        /* Header Section / Kop Surat */
        .header-section {
            margin-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
            text-align: center;
        }
        .header-logo {
            max-height: 80px;
        }
        .report-title-container {
            margin-top: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.75px;
        }
        
        /* Metadata Grid Box */
        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .metadata-table td {
            padding: 10px 14px;
            border: none;
            vertical-align: top;
            font-size: 11px;
        }
        .metadata-label {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .metadata-value {
            display: block;
            font-size: 11px;
            color: #0f172a;
            font-weight: bold;
        }
        
        /* Section Title */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            margin-top: 20px;
            margin-bottom: 10px;
            border-left: 3px solid #3b82f6;
            padding-left: 8px;
        }
        
        /* Report Table Styling */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .report-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 6px;
            border: 1px solid #1e3a8a;
            text-align: left;
            letter-spacing: 0.5px;
        }
        .report-table td {
            padding: 8px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 10px;
        }
        .report-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Custom visual badges */
        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            text-align: center;
            color: #ffffff;
        }
        .badge-success {
            background-color: #10b981;
        }
        .badge-danger {
            background-color: #ef4444;
        }
        .badge-secondary {
            background-color: #64748b;
        }
        
        /* APAR and Location Details */
        .item-spec {
            font-weight: bold;
            color: #0f172a;
        }
        .location-main {
            font-weight: 500;
            color: #334155;
        }
        .location-sub {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        
        /* QR Code image style */
        .qr-code-img {
            border: 1px solid #cbd5e1;
            padding: 2px;
            background-color: #ffffff;
            border-radius: 2px;
        }
        
        /* Page break rules */
        .report-table tr {
            page-break-inside: avoid;
        }
        
        /* Signature Table */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            padding: 0;
        }
        .signature-box {
            width: 250px;
            float: right;
            text-align: center;
        }
        .signature-title {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 50px;
        }
        .signature-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
            margin: 0;
        }
        .signature-role {
            font-size: 9px;
            color: #64748b;
            margin: 0;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    @if ($kop && $kop->image)
        <div class="header-section">
            <img src="{{ public_path("storage/" . $kop->image) }}" class="header-logo" alt="Logo Perusahaan">
        </div>
    @endif

    <div class="report-title-container">
        <h2 class="report-title">Laporan Inventaris APAR</h2>
    </div>

    <table class="metadata-table">
        <tr>
            <td style="width: 33.33%;">
                <span class="metadata-label">Kode Pelanggan</span>
                <span class="metadata-value">{{ auth()->user()->kode_customer }}</span>
            </td>
            <td style="width: 33.33%;">
                <span class="metadata-label">Total APAR</span>
                <span class="metadata-value">{{ count($apar) }} Unit</span>
            </td>
            <td style="width: 33.33%;">
                <span class="metadata-label">Tanggal Cetak</span>
                <span class="metadata-value">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Detail Unit APAR</div>
    
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 3%; text-align: center;">#</th>
                <th style="width: 12%; text-align: center;">Kode</th>
                <th style="width: 10%;">Spesifikasi</th>
                <th style="width: 15%;">Lokasi & Penempatan</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 10%;">Cek Terakhir</th>
                <th style="width: 8%;">Pressure</th>
                <th style="width: 8%;">Selang</th>
                <th style="width: 8%;">Head Valve</th>
                <th style="width: 9%;">Korosi</th>
                <th style="width: 9%;">Expired</th>
            </tr>
        </thead>
        <tbody>
            @foreach($apar as $i => $item)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td style="text-align: center;">
                        <div style="font-weight: bold; color: #0f172a; font-size: 8px; margin-top: 3px; letter-spacing: 0.2px;">{{ $item->kode_barang }}</div>
                    </td>
                    <td>
                        <div class="item-spec">{{ $item->media }}</div>
                        <div style="font-size: 9px; color: #64748b; margin-top: 2px;">{{ $item->kapasitas }}kg</div>
                    </td>
                    <td>
                        <div class="location-main">{{ $item->lokasi ?? '-' }}</div>
                        <div class="location-sub">{{ $item->titik_penempatan_id ?? '-' }}</div>
                    </td>
                    <td>
                        @if(strtolower($item->status ?? '') === 'ok')
                            <span class="badge badge-success">OK</span>
                        @elseif(strtolower($item->status ?? '') === 'rusak')
                            <span class="badge badge-danger">RUSAK</span>
                        @else
                            <span class="badge badge-secondary">{{ strtoupper($item->status ?? 'BELUM CEK') }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $item->last_inspection ? \Carbon\Carbon::parse($item->last_inspection)->translatedFormat('d-m-Y') : '-' }}
                    </td>
                    <td>{{ $item->pressure ?? '-' }}</td>
                    <td>{{ $item->hose ?? '-' }}</td>
                    <td>{{ $item->head_valve ?? '-' }}</td>
                    <td>{{ $item->korosi ?? '-' }}</td>
                    <td>{{ $item->expired ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td></td>
            <td style="width: 300px;">
                <div class="signature-box">
                    <div class="signature-title">Semarang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                    <div class="signature-title" style="margin-bottom: 60px;">Mengetahui,</div>
                    <div class="signature-name">{{ auth()->user()->name }}</div>
                    <div class="signature-role">Administrator</div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

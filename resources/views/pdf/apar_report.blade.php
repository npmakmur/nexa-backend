<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inspeksi APAR</title>
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
            /* width: 100%; */
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
            vertical-align: top;
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
            margin-bottom: 4px;
        }
        .badge-success {
            background-color: #10b981;
        }
        .badge-danger {
            background-color: #ef4444;
        }
        
        /* APAR and Location Details */
        .item-code {
            font-weight: bold;
            color: #0f172a;
            font-size: 10px;
        }
        .item-spec {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
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
        
        /* Check details inside cells */
        .check-detail {
            font-size: 9px;
            color: #475569;
            line-height: 1.3;
        }
        .check-text {
            color: #0f172a;
            font-weight: 500;
        }
        .thumbnail {
            display: block;
            max-width: 75px;
            max-height: 75px;
            border: 1px solid #cbd5e1;
            margin-top: 5px;
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
            <img src="{{ public_path('storage/' . $kop->image) }}" class="header-logo">
        </div>
    @endif

    <div class="report-title-container">
        <h2 class="report-title">Laporan Inspeksi APAR</h2>
    </div>

    <table class="metadata-table">
        <tr>
            <td style="width: 33.33%;">
                <span class="metadata-label">No Jadwal</span>
                <span class="metadata-value">{{ $agenda->no_jadwal }}</span>
            </td>
            <td style="width: 33.33%;">
                <span class="metadata-label">Inspeksi</span>
                <span class="metadata-value">{{ $agenda->inspeksi_title }}</span>
            </td>
            <td style="width: 33.33%;">
                <span class="metadata-label">PIC</span>
                <span class="metadata-value">{{ $agenda->inspection_name }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="metadata-label">Jumlah APAR</span>
                <span class="metadata-value">{{ $agenda->jumlah_apar }} Unit</span>
            </td>
            <td colspan="2">
                <span class="metadata-label">Tanggal Mulai</span>
                <span class="metadata-value">{{ \Carbon\Carbon::parse($agenda->tgl_mulai)->translatedFormat('d F Y') }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Detail Hasil Inspeksi APAR</div>
    
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 3%; text-align: center;">#</th>
                <th style="width: 12%;">Alat Pemadam</th>
                <th style="width: 12%;">Lokasi</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 8%;">Tanggal Cek</th>
                <th style="width: 11%;">Pressure</th>
                <th style="width: 12%;">Selang</th>
                <th style="width: 11%;">Head Valve</th>
                <th style="width: 11%;">Korosi</th>
                <th style="width: 11%;">Expired</th>
            </tr>
        </thead>
        <tbody>
            @foreach($apar as $i => $item)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td>
                        <div class="item-code">{{ $item->kode_barang }}</div>
                        <div class="item-spec">{{ $item->media }} ({{ $item->kapasitas }}kg)</div>
                    </td>
                    <td>
                        <div class="location-main">{{ $item->lokasi }}</div>
                    </td>
                    <td>
                        @if(strtolower($item->status ?? '') === 'ok')
                            <span class="badge badge-success">OK</span>
                        @else
                            <span class="badge badge-danger">RUSAK</span>
                        @endif
                        <div class="item-spec">QC: {{ $item->qc_name }}</div>
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal_cek)->translatedFormat('d-m-Y') }}
                    </td>
                    <td>
                        <div class="check-detail">Kondisi: <span class="check-text">{{ $item->detail_pressure }}</span></div>
                        @if($item->pressure_img)
                            <img src="{{ public_path('storage/' . $item->pressure_img) }}" class="thumbnail" alt="Pressure">
                        @endif
                    </td>
                    <td>
                        <div class="check-detail">Selang: <span class="check-text">{{ is_null($item->checklist_selang) ? '-' : ($item->checklist_selang ? 'Ada' : 'Tidak Ada') }}</span></div>
                        <div class="check-detail" style="margin-top: 2px;">Kondisi: <span class="check-text">{{ $item->detail_hose ?? '-' }}</span></div>
                        @if($item->hose_img)
                            <img src="{{ public_path('storage/' . $item->hose_img) }}" class="thumbnail" alt="Selang">
                        @endif
                    </td>
                    <td>
                        <div class="check-detail">Kondisi: <span class="check-text">{{ $item->detail_head_valve }}</span></div>
                        @if($item->head_valve_img)
                            <img src="{{ public_path('storage/' . $item->head_valve_img) }}" class="thumbnail" alt="Head Valve">
                        @endif
                    </td>
                    <td>
                        <div class="check-detail">Kondisi: <span class="check-text">{{ $item->detail_korosi }}</span></div>
                        @if($item->korosi_img)
                            <img src="{{ public_path('storage/' . $item->korosi_img) }}" class="thumbnail" alt="Korosi">
                        @endif
                    </td>
                    <td>
                        <div class="check-detail">Kondisi: <span class="check-text">{{ $item->detail_expired }}</span></div>
                        @if($item->expired_img)
                            <img src="{{ public_path('storage/' . $item->expired_img) }}" class="thumbnail" alt="Expired">
                        @endif
                    </td>
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
                    <div class="signature-title" style="margin-bottom: 60px;">Dibuat Oleh,</div>
                    <div class="signature-name">{{ $agenda->inspection_name }}</div>
                    <div class="signature-role">PIC Inspeksi</div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

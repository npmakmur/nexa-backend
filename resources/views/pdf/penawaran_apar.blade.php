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
        
        .summary-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .terms-conditions {
            width: 60%;
            padding-right: 20px;
        }
        .amount-summary {
            width: 40%;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 15px;
        }
        .amount-label {
            font-weight: normal;
        }
        .amount-value {
            font-weight: bold;
        }
        .total-row {
            background-color: #007bff;
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-label {
            font-size: 14px;
            font-weight: bold;
        }
        .total-value {
            font-size: 16px;
            font-weight: bold;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .bank-details {
            width: 50%; /* Adjusted for better balance */
        }
        .signature {
            width: 50%; /* Adjusted for better balance */
            text-align: center; /* Centered the signature within its div */
        }
        .signature img {
            max-width: 150px;
            height: auto;
            display: block; /* Make image a block element to center with margin auto */
            margin-left: auto;
            margin-right: auto;
        }
        .signature p {
            margin-top: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <img src="{{ public_path($kop->image) }}" width="100%" alt="">
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <table style="border: none; width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="border: none; padding: 4px; background-color: #ffffff;">Quotation From</td>
                        <td style="border: none; padding: 4px; background-color: #ffffff;">Quotation For</td>
                        <td style="border: none; padding: 4px; background-color: #ffffff;">Details</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px; background-color: #ffffff;">PT. Tan Anugrah Sejahtera</td>
                        <td style="border: none; padding: 4px; background-color: #ffffff;">{{ $customer->nama_customer }}</td>
                        <td style="border: none; padding: 4px; background-color: #ffffff;"><span>Quotation No # </span>TAS-XII-Q00797</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px; background-color: #ffffff;">Semarang, Indonesia</td>
                        <td style="border: none; padding: 4px; background-color: #ffffff;">Indonesia</td>
                        <td style="border: none; padding: 4px; background-color: #ffffff;"><span>Quotation Date </span>{{ \Carbon\Carbon::now()->format('M d, Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    @php
        $subtotal_all_items = collect($list_penawaran)->sum('subtotal'); 
        $ppn_amount = $subtotal_all_items * 0.11;
        $grand_total = $subtotal_all_items + $ppn_amount;
    @endphp
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amount</th>
                <th>PPN Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list_penawaran as $i => $item)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $item['part'] }}</td>
                <td>{{ $item['qty'] }}</td>
                <td>{{ number_format($item['harga'], 0, ',', '.') }}</td>
                <td>{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                <td>11%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; background-color: #ffffff; border: none;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 10px; border: none; background-color: #ffffff;">
                <p style="margin: 0; padding: 0;">Terms and Conditions</p>
                <p style="margin: 5px 0 0;">Franco : Jakarta</p>
                <p style="margin: 5px 0 0;">Pembayaran : CBD</p>
                <p style="margin: 5px 0 0;">Validasi Penawaran : 7 Hari Kerja</p>
                <p style="margin: 5px 0 0;">Stok tidak terikat</p>
            </td>

            <td style="width: 50%; vertical-align: top; padding: 10px; border: none; background-color: #ffffff;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        <td style="padding: 5px 0; text-align: left; border: none; background-color: #ffffff;">Amount</td>
                        <td style="padding: 5px 0; text-align: right; border: none; background-color: #ffffff;">IDR {{ number_format($subtotal_all_items, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; text-align: left; border: none; background-color: #ffffff;">PPN</td>
                        <td style="padding: 5px 0; text-align: right; border: none; background-color: #ffffff;">IDR {{ number_format($ppn_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr style="background-color: #007bff;">
                        <td style="padding: 5px; font-weight: bold; text-align: left; border: none; color: #ffffff">Total (IDR)</td>
                        <td style="padding: 5px; font-weight: bold; text-align: right; border: none; color:#ffffff">IDR {{ number_format($grand_total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table style="width: 100%; border-collapse: collapse; border: none; font-family: Arial, sans-serif;">
        <tr>
            <td style="width: 33.33%; border: none;"></td>

            <td style="width: 33.33%; border: none;"></td>

            <td style="width: 33.33%; text-align: center; vertical-align: top; padding: 10px; border: none;">
                <img src="{{ public_path('surat/ttd.png') }}" 
                    style="width: 100%; max-width: 150px; height: auto;" 
                    alt="Signature">
                <p style="margin: 5px 0 0;">Tristan</p>
            </td>
        </tr>
    </table>

    <div class="bank-details">
        <h3>Bank Details</h3>
        <p><strong>Account Name</strong>: RAHSIWI BITRISTAN PAMUNGKAS</p>
        <p><strong>Account Number</strong>: 2460623530</p>
        <p><strong>Bank</strong>: BCA</p>
    </div>
</body>
</html>
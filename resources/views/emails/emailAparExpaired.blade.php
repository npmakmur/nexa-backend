<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Extinguisher Expiration Warning</title>
    <style>
        /* Critical Inline Styles for Email Compatibility */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e0e0e0; }
        .header { background-color: #e8173eff; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; line-height: 1.6; color: #333333; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #e0e0e0; }
        .button-link { display: inline-block; padding: 10px 20px; margin-top: 20px; background-color: #4CAF50; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #f0f0f0; }
        .warning { color: #cc0000; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <h2>Fire Extinguisher Expiration Warning</h2>
        </div>
        
        <div class="content">
            
            <p>Dear {{ $customer->nama_customer }},</p>
            
            <p>We are sending this email to remind you that **{{ $apars->count() }} unit(s)** of Portable Fire Extinguishers (APAR/FIRE EXTINGUISHERS) under your supervision will **expire in less than 30 days**.</p>

            <p class="warning">Important: Expired fire extinguishers are not guaranteed to function optimally in an emergency.</p>

            <h3>Details of Units Requiring Attention:</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Medium</th>
                        <th>Capacity</th>
                        <th>Expiration Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($apars as $apar)
                    <tr>
                        <td>{{ $apar->kode_barang }}</td>
                        <td>{{ $apar->media }}</td>
                        <td>{{ $apar->kapasitas }} Kg</td>
                        <td class="warning">{{ \Carbon\Carbon::parse($apar->tgl_kadaluarsa)->format('F d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Please take immediate action to perform a **refill (recharge)** or **replacement** of these extinguishers to maintain safety standards and comply with applicable regulations.</p>
            <p>Thank you for your attention to safety.</p>
            <p>Sincerely,<br>Your Inventory Security Team</p>
        </div>

        <div class="footer">
            <p>This email was sent automatically by the system. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
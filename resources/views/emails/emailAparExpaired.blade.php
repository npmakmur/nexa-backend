<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan Kedaluwarsa APAR</title>
    <style>
        /* Gaya Inline Kritis untuk Kompatibilitas Email */
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
            <h2>Peringatan Kedaluwarsa APAR</h2>
        </div>
        
        <div class="content">
            
            <p>Yth. {{ $customer->nama_customer }}</p>
            
            <p>Kami mengirimkan email ini untuk mengingatkan Anda bahwa {{ $apars->count() }} unit Alat Pemadam Api Ringan (APAR) di bawah pengawasan Anda akan segera **kedaluwarsa dalam waktu kurang dari 30 hari**.</p>

            <p class="warning">Penting: APAR yang kedaluwarsa tidak menjamin fungsi optimal dalam situasi darurat.</p>

            <h3>Detail Unit yang Perlu Diperhatikan:</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Kode Barang</th>
                        <th>Media</th>
                        <th>Kapasitas</th>
                        <th>Tanggal Kedaluwarsa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($apars as $apar)
                    <tr>
                        <td>{{ $apar->kode_barang }}</td>
                        <td>{{ $apar->media }}</td>
                        <td>{{ $apar->kapasitas }} Kg</td>
                        <td class="warning">{{ \Carbon\Carbon::parse($apar->tgl_kadaluarsa)->format('d F Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Mohon segera tindak lanjuti untuk melakukan pengisian ulang (refill) atau penggantian APAR tersebut untuk menjaga standar keselamatan dan mematuhi peraturan yang berlaku.</p>
            <p>Terima kasih atas perhatian Anda terhadap keselamatan.</p>
            <p>Salam hormat,<br>Tim Keamanan Inventaris Anda</p>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem. Mohon jangan balas email ini.</p>
        </div>
    </div>
</body>
</html>

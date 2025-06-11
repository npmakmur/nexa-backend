<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sub }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .container {
            background-color: #ffffff;
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            color: #333333;
        }
        p {
            font-size: 16px;
            color: #555555;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            margin: 20px 0;
            color: #007bff;
            letter-spacing: 4px;
        }
        .footer {
            font-size: 14px;
            color: #999999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hai {{ $nama }},</h2>
        <p>{{ $teks }}</p>
        <div class="code">{{ $code }}</div>
        <p>Jika Anda tidak merasa melakukan permintaan ini, abaikan saja email ini.</p>
        <div class="footer">
            &copy; 2025 Nama Perusahaan. Semua hak dilindungi.
        </div>
    </div>
</body>
</html>

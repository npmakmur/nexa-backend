<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; }
        .qr-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .qr-item {
            width: 200px;
            text-align: center;
        }
        img {
            max-width: 100%;
        }
    </style>
</head>
<body>
    <h2>QR Codes</h2>
    <div class="qr-container">
        @foreach($qrCodes as $item)
        <div class="qr-item">
            <img src="{{ public_path('storage/' . $item['path']) }}" alt="QR {{ $item['barcode'] }}">
            <p>{{ $item['barcode'] }}</p>
        </div>
        @endforeach
    </div>
</body>
</html>

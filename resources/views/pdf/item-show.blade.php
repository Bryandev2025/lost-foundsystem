<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Item Sheet</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .box {
            border: 1px solid #333;
            padding: 12px;
            border-radius: 6px;
        }

        .row {
            margin-bottom: 8px;
        }

        .label {
            font-weight: bold;
        }

        img {
            max-width: 180px;
            max-height: 180px;
            border: 1px solid #ccc;
            padding: 3px;
        }
    </style>
</head>

<body>
    <h2>Lost & Found — Item Sheet</h2>

    <div class="box">
        <div class="row"><span class="label">Item ID:</span> {{ $item->id }}</div>
        <div class="row"><span class="label">Type:</span> {{ strtoupper($item->type) }}</div>
        <div class="row"><span class="label">Title:</span> {{ $item->title }}</div>
        <div class="row"><span class="label">Category:</span> {{ $item->category }}</div>
        <div class="row"><span class="label">Location:</span> {{ $item->location }}</div>
        <div class="row"><span class="label">Status:</span> {{ strtoupper($item->status) }}</div>
        <div class="row"><span class="label">Reported By:</span> {{ optional($item->reporter)->name }}</div>

        @if($item->image_path)
            <div class="row">
                <span class="label">Image:</span><br>
                <img src="{{ public_path('storage/' . $item->image_path) }}">
            </div>
        @endif

        @if($qrPngPath)
            <div class="row">
                <span class="label">QR Code:</span><br>
                <img src="{{ $qrPngPath }}">
            </div>
        @endif
    </div>
</body>

</html>
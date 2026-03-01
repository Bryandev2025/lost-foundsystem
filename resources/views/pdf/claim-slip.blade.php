<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Claim Slip</title>
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
    </style>
</head>

<body>
    <h2>Lost & Found — Claim Slip</h2>

    <div class="box">
        <div class="row"><span class="label">Claim ID:</span> {{ $claim->id }}</div>
        <div class="row"><span class="label">Item:</span> {{ optional($claim->item)->title }} (ID:
            {{ $claim->item_id }})</div>
        <div class="row"><span class="label">Claimer:</span> {{ optional($claim->claimer)->name }}</div>
        <div class="row"><span class="label">Status:</span> {{ strtoupper($claim->status) }}</div>
        <div class="row"><span class="label">Reviewed By:</span> {{ optional($claim->reviewer)->name }}</div>
        <div class="row"><span class="label">Reviewed At:</span> {{ $claim->reviewed_at?->format('Y-m-d H:i') }}</div>

        <hr>
        <p><b>Signature (Claimer):</b> ____________________________</p>
        <p><b>Signature (Staff):</b> ______________________________</p>
        <p><b>Date Released:</b> _________________________________</p>
    </div>
</body>

</html>
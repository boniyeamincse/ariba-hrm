<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { margin-bottom: 16px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f2f4f7; }
        .footer { margin-top: 18px; font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <h1>E-Prescription #{{ $prescription->id }}</h1>
    <div class="meta">
        <div>Generated At: {{ $generatedAt->format('Y-m-d H:i:s') }}</div>
        <div>Consultation ID: {{ $prescription->consultation_id }}</div>
        <div>Prescribed By: {{ $prescription->prescribed_by ?? 'N/A' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Duration</th>
                <th>Route</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescription->items as $item)
                <tr>
                    <td>{{ $item->medicine_name }}</td>
                    <td>{{ $item->dosage }}</td>
                    <td>{{ $item->frequency }}</td>
                    <td>{{ $item->duration }}</td>
                    <td>{{ $item->route }}</td>
                    <td>{{ $item->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Instructions: {{ $prescription->instructions ?? 'N/A' }}
    </div>
</body>
</html>

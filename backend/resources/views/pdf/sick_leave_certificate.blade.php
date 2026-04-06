<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sick Leave Certificate</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        .section { margin-top: 12px; }
    </style>
</head>
<body>
    <h1>Sick Leave Certificate #{{ $certificate->id }}</h1>
    <div>Generated At: {{ $generatedAt->format('Y-m-d H:i:s') }}</div>

    <div class="section">
        <div>Patient ID: {{ $certificate->patient_id }}</div>
        <div>Consultation ID: {{ $certificate->consultation_id }}</div>
        <div>Doctor ID: {{ $certificate->doctor_id ?? 'N/A' }}</div>
    </div>

    <div class="section">
        <div>Leave From: {{ $certificate->leave_from->format('Y-m-d') }}</div>
        <div>Leave To: {{ $certificate->leave_to->format('Y-m-d') }}</div>
        <div>Total Days: {{ $certificate->days_count }}</div>
    </div>

    <div class="section">
        <div>Reason: {{ $certificate->reason ?? 'N/A' }}</div>
    </div>

    <div class="section" style="margin-top: 28px;">
        <div>Doctor Signature: {{ $certificate->doctor_signature_name ?? 'Digitally Signed' }}</div>
    </div>
</body>
</html>

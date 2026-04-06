<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Referral Letter</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 6px; }
        .section { margin-top: 12px; }
        .label { font-weight: 700; }
    </style>
</head>
<body>
    <h1>Referral Letter #{{ $referral->id }}</h1>
    <div>Generated At: {{ $generatedAt->format('Y-m-d H:i:s') }}</div>

    <div class="section">
        <div><span class="label">Patient ID:</span> {{ $referral->patient_id }}</div>
        <div><span class="label">Consultation ID:</span> {{ $referral->consultation_id }}</div>
        <div><span class="label">Referral Type:</span> {{ strtoupper($referral->referral_type) }}</div>
    </div>

    <div class="section">
        <div><span class="label">Target Department:</span> {{ $referral->target_department ?? 'N/A' }}</div>
        <div><span class="label">Target Specialist:</span> {{ $referral->target_specialist ?? 'N/A' }}</div>
        <div><span class="label">External Facility:</span> {{ $referral->external_facility ?? 'N/A' }}</div>
    </div>

    <div class="section">
        <div class="label">Reason</div>
        <div>{{ $referral->reason ?? 'N/A' }}</div>
    </div>

    <div class="section">
        <div class="label">Clinical Notes</div>
        <div>{{ $referral->clinical_notes ?? 'N/A' }}</div>
    </div>
</body>
</html>

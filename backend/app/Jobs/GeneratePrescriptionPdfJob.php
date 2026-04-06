<?php

namespace App\Jobs;

use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePrescriptionPdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $prescriptionId)
    {
    }

    public function handle(): void
    {
        $prescription = Prescription::query()->with(['items', 'consultation'])->find($this->prescriptionId);
        if (! $prescription) {
            return;
        }

        $pdf = Pdf::loadView('pdf.prescription', [
            'prescription' => $prescription,
            'generatedAt' => now(),
        ]);

        $tenantId = $prescription->tenant_id ?? 'shared';
        $path = 'tenants/'.$tenantId.'/opd/prescriptions/prescription-'.$prescription->id.'.pdf';

        $disk = config('filesystems.default', 'local');
        Storage::disk($disk)->put($path, $pdf->output());

        $prescription->update([
            'pdf_path' => $path,
            'pdf_generated_at' => now(),
        ]);
    }
}

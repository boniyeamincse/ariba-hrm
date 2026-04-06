<?php

namespace App\Jobs;

use App\Models\SickLeaveCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateSickLeaveCertificatePdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $certificateId)
    {
    }

    public function handle(): void
    {
        $certificate = SickLeaveCertificate::query()->find($this->certificateId);
        if (! $certificate) {
            return;
        }

        $pdf = Pdf::loadView('pdf.sick_leave_certificate', [
            'certificate' => $certificate,
            'generatedAt' => now(),
        ]);

        $tenantId = $certificate->tenant_id ?? 'shared';
        $path = 'tenants/'.$tenantId.'/opd/sick-leave/sick-leave-'.$certificate->id.'.pdf';

        $disk = config('filesystems.default', 'local');
        Storage::disk($disk)->put($path, $pdf->output());

        $certificate->update([
            'pdf_path' => $path,
            'generated_at' => now(),
        ]);
    }
}

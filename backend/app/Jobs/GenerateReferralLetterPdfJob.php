<?php

namespace App\Jobs;

use App\Models\Referral;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateReferralLetterPdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $referralId)
    {
    }

    public function handle(): void
    {
        $referral = Referral::query()->with(['consultation', 'patient'])->find($this->referralId);
        if (! $referral) {
            return;
        }

        $pdf = Pdf::loadView('pdf.referral_letter', [
            'referral' => $referral,
            'generatedAt' => now(),
        ]);

        $tenantId = $referral->tenant_id ?? 'shared';
        $path = 'tenants/'.$tenantId.'/opd/referrals/referral-'.$referral->id.'.pdf';

        $disk = config('filesystems.default', 'local');
        Storage::disk($disk)->put($path, $pdf->output());

        $referral->update([
            'letter_pdf_path' => $path,
            'letter_generated_at' => now(),
            'status' => 'letter_generated',
        ]);
    }
}

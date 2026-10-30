<?php

namespace App\Http\Controllers;

use App\Models\PermitApplication;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * A print-ready permit. The QR code links to the public verification page.
 */
class PermitPrintController extends Controller
{
    public function __invoke(PermitApplication $application): View
    {
        Gate::authorize('print', $application);

        $application->load('applicant.applicantProfile', 'decider:id,name');
        $verifyUrl = route('permits.verify', $application->verification_code);
        $qr = (new Writer(new ImageRenderer(new RendererStyle(160, 1), new SvgImageBackEnd)))->writeString($verifyUrl);

        return view('permits.print', [
            'permit' => $application,
            'profile' => $application->applicant?->applicantProfile,
            'profession' => $application->applicant?->profession,
            'verifyUrl' => $verifyUrl,
            'qrSvg' => $qr,
        ]);
    }
}

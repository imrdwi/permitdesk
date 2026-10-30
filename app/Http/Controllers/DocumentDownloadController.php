<?php

namespace App\Http\Controllers;

use App\Models\ApplicantDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Supporting documents are private: only their owner and office staff may open them.
 */
class DocumentDownloadController extends Controller
{
    public function __invoke(Request $request, ApplicantDocument $document): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->isOffice() || $document->user_id === $user->id, 403);

        return Storage::disk('local')->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Applicant;

use App\DocumentType;
use App\Http\Controllers\Controller;
use App\Models\ApplicantDocument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $uploaded = $user->documents()->get()->keyBy(fn (ApplicantDocument $document) => $document->type->value);

        return Inertia::render('applicant/Documents', [
            'documents' => array_map(fn (DocumentType $type) => [
                'type' => $type->value,
                'label' => $type->label(),
                'uploaded' => ($document = $uploaded->get($type->value)) ? [
                    'id' => $document->id,
                    'original_name' => $document->original_name,
                    'size' => $document->size,
                    'uploaded_at' => $document->updated_at,
                ] : null,
            ], $user->profession?->requiredDocuments() ?? []),
        ]);
    }

    /**
     * Upload or replace one required document.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $required = array_map(fn (DocumentType $type) => $type->value, $user->profession?->requiredDocuments() ?? []);

        $validated = $request->validate([
            'type' => ['required', Rule::in($required)],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $type = DocumentType::from($validated['type']);
        $file = $request->file('file');
        abort_unless($file !== null, 422);

        $existing = $user->documents()->where('type', $type)->first();
        $path = $file->store("documents/{$user->id}", 'local');

        $user->documents()->updateOrCreate(['type' => $type], [
            'path' => (string) $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => (string) $file->getMimeType(),
            'size' => (int) $file->getSize(),
        ]);

        if ($existing !== null) {
            Storage::disk('local')->delete($existing->path);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':document uploaded.', ['document' => $type->label()])]);

        return to_route('applicant.documents.index');
    }

    public function destroy(Request $request, ApplicantDocument $document): RedirectResponse
    {
        abort_unless($document->user_id === $request->user()?->id, 403);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Document removed.')]);

        return to_route('applicant.documents.index');
    }
}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Permit verification · {{ config('app.name') }}</title>
    <style>
        body { font-family: "Instrument Sans", system-ui, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #fafafa; color: #111; }
        .card { max-width: 420px; width: calc(100% - 32px); background: #fff; border: 1px solid #e4e4e7; border-radius: 12px; padding: 28px; }
        .status { display: inline-block; font-weight: 600; padding: 4px 10px; border-radius: 999px; font-size: 14px; }
        .ok { background: #dcfce7; color: #166534; }
        .bad { background: #fee2e2; color: #991b1b; }
        dl { display: grid; grid-template-columns: auto 1fr; gap: 6px 16px; font-size: 14px; margin: 20px 0 0; }
        dt { color: #52525b; }
        dd { margin: 0; }
    </style>
</head>
<body>
    <main class="card">
        <h1 style="font-size: 18px; margin: 0 0 12px;">Practice permit verification</h1>
        @if ($permit === null)
            <span class="status bad">Not found</span>
            <p>No permit matches this code. The document may not be genuine.</p>
        @else
            <span class="status {{ $valid ? 'ok' : 'bad' }}">{{ $valid ? 'Valid' : $permit->status->label() }}</span>
            <dl>
                <dt>Permit</dt>
                <dd>{{ $permit->permit_number }}</dd>
                <dt>Holder</dt>
                <dd>{{ $permit->applicant?->applicantProfile?->full_name }}</dd>
                <dt>Profession</dt>
                <dd>{{ $permit->applicant?->profession?->label() }}</dd>
                <dt>Location</dt>
                <dd>{{ $permit->practice_name }}</dd>
                <dt>Valid until</dt>
                <dd>{{ $permit->expires_on?->translatedFormat('j F Y') }}</dd>
            </dl>
        @endif
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practice permit {{ $permit->permit_number }}</title>
    <style>
        @page { size: A4; margin: 18mm; }
        * { box-sizing: border-box; }
        body { font-family: "Instrument Sans", system-ui, sans-serif; color: #111; margin: 0; background: #f4f4f5; }
        .sheet { max-width: 210mm; margin: 24px auto; background: #fff; padding: 20mm; border: 1px solid #e4e4e7; }
        header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111; padding-bottom: 12px; }
        h1 { font-size: 22px; margin: 0 0 4px; letter-spacing: .02em; }
        .muted { color: #52525b; font-size: 13px; }
        .number { font-family: ui-monospace, monospace; font-size: 15px; }
        dl { display: grid; grid-template-columns: 200px 1fr; gap: 8px 16px; margin: 24px 0; font-size: 14px; }
        dt { color: #52525b; }
        dd { margin: 0; }
        .validity { border: 1px solid #111; padding: 12px 16px; font-size: 14px; }
        footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; }
        .qr svg { width: 120px; height: 120px; }
        .actions { max-width: 210mm; margin: 0 auto; text-align: right; }
        .actions button { font: inherit; padding: 8px 16px; border-radius: 6px; border: 1px solid #111; background: #111; color: #fff; cursor: pointer; }
        @media print { body { background: #fff; } .sheet { border: 0; margin: 0; padding: 0; } .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Print or save as PDF</button></div>
    <main class="sheet">
        <header>
            <div>
                <h1>Practice Permit</h1>
                <div class="muted">{{ config('app.name') }} · {{ $profession?->label() }}{{ $permit->is_temporary ? ' · Temporary location' : '' }}</div>
            </div>
            <div class="number">{{ $permit->permit_number }}</div>
        </header>

        <dl>
            <dt>Name</dt>
            <dd>{{ $profile?->full_name }}</dd>
            <dt>Place and date of birth</dt>
            <dd>{{ $profile?->birth_place }}, {{ $profile?->birth_date?->translatedFormat('j F Y') }}</dd>
            <dt>Address</dt>
            <dd>{{ $profile?->address }}</dd>
            @if ($profession?->requiresLicense())
                <dt>{{ $profession->licenseLabel() }} number</dt>
                <dd>{{ $profile?->license_number }}</dd>
            @else
                <dt>University</dt>
                <dd>{{ $profile?->university }}</dd>
            @endif
            <dt>Practice location</dt>
            <dd>{{ $permit->practice_name }}<br><span class="muted">{{ $permit->practice_address }}</span></dd>
        </dl>

        <div class="validity">
            Issued {{ $permit->decided_at?->translatedFormat('j F Y') }} and valid until
            <strong>{{ $permit->expires_on?->translatedFormat('j F Y') }}</strong>.
            @if ($permit->status !== \App\PermitStatus::Approved)
                <strong>This permit is {{ strtolower($permit->status->label()) }}.</strong>
            @endif
        </div>

        <footer>
            <div class="qr">
                {!! $qrSvg !!}
                <div class="muted">Verify at {{ $verifyUrl }}</div>
            </div>
            <div style="text-align: center; min-width: 220px;">
                <div class="muted">Approved by</div>
                <div style="height: 64px;"></div>
                <div>{{ $permit->decider?->name }}</div>
            </div>
        </footer>
    </main>
</body>
</html>

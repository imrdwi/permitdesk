<?php

namespace App\Console\Commands;

use App\Models\PermitApplication;
use App\Notifications\PermitExpiringSoon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('permits:remind {--days=30 : Remind about permits expiring within this many days}')]
#[Description('Email applicants whose permits expire soon, once per permit')]
class RemindExpiringPermits extends Command
{
    public function handle(): int
    {
        $count = 0;

        PermitApplication::query()
            ->expiringWithin((int) $this->option('days'))
            ->whereNull('expiry_reminded_at')
            ->with('applicant')
            ->each(function (PermitApplication $permit) use (&$count): void {
                $permit->applicant?->notify(new PermitExpiringSoon($permit));
                $permit->forceFill(['expiry_reminded_at' => now()])->save();
                $count++;
            });

        $this->components->info("Sent {$count} reminder(s).");

        return self::SUCCESS;
    }
}

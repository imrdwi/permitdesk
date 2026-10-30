<?php

namespace App\Console\Commands;

use App\Models\PermitApplication;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('permits:expire')]
#[Description('Mark approved permits past their expiry date as expired')]
class ExpirePermits extends Command
{
    public function handle(): int
    {
        $count = 0;

        PermitApplication::query()->lapsed()->each(function (PermitApplication $permit) use (&$count): void {
            $permit->expire();
            $count++;
        });

        $this->components->info("Expired {$count} permit(s).");

        return self::SUCCESS;
    }
}

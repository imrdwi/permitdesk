<?php

namespace App\Exceptions;

use App\PermitStatus;
use DomainException;

class InvalidPermitTransition extends DomainException
{
    public static function between(PermitStatus $from, PermitStatus $to): self
    {
        return new self("A permit that is {$from->label()} cannot become {$to->label()}.");
    }
}

<?php

declare(strict_types=1);

namespace CourierInvoice\Domain\Auth;

class LoginIdentifier
{
    public function __construct(
        public string $type,
        public string $subtype,
        public string $id,
        public string $raw
    ) {
    }
}
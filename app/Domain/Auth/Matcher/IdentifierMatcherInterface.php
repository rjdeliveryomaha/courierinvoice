<?php

declare(strict_types=1);

namespace CourierInvoice\Domain\Auth\Matcher;

use CourierInvoice\Domain\Auth\LoginIdentifier;

interface IdentifierMatcherInterface
{
    public function matches(string $username): bool;

    public function resolve(string $username): LoginIdentifier;
}
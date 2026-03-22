<?php

declare(strict_types=1);

namespace CourierInvoice\Domain\Auth\Matcher;

use CourierInvoice\Domain\Auth\LoginIdentifier;
use InvalidArgumentException;

class ClientNumericMatcher implements IdentifierMatcherInterface
{
    public function matches(string $username): bool
    {
        $username = strtolower(trim($username));

        return ctype_digit($username) === 1;
    }

    public function resolve(string $username): LoginIdentifier
    {
        $username = strtolower(trim($username));

        if (!$this->matches($username)) {
            throw new InvalidArgumentException('Username does not match client pattern.');
        }
		
        return new LoginIdentifier(
            type: 'client',
            subtype: 'repeat',
            id: $username,
            raw: $username
        );
    }
}
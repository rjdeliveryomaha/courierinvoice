<?php

declare(strict_types=1);

namespace CourierInvoice\Domain\Auth\Matcher;

use CourierInvoice\Domain\Auth\LoginIdentifier;
use InvalidArgumentException;

class DriverMatcher implements IdentifierMatcherInterface
{
    public function matches(string $username): bool
    {
        $username = strtolower(trim($username));

        return preg_match('/^driver(\d+)$/', $username) === 1;
    }

    public function resolve(string $username): LoginIdentifier
    {
        $username = strtolower(trim($username));

        if (!$this->matches($username)) {
            throw new InvalidArgumentException('Username does not match driver pattern.');
        }

        preg_match('/^driver(\d+)$/', $username, $matches);

        return new LoginIdentifier(
            type: 'employee',
            subtype: 'driver',
            id: $matches[1],
            raw: $username
        );
    }
}
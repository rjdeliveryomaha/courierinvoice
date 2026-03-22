<?php

declare(strict_types=1);

namespace CourierInvoice\Domain\Auth\Matcher;

use CourierInvoice\Domain\Auth\LoginIdentifier;
use InvalidArgumentException;

class OrganizationMatcher implements IdentifierMatcherInterface
{
    public function matches(string $username): bool
    {
        $username = strtolower(trim($username));

        return $username !== '';
    }

    public function resolve(string $username): LoginIdentifier
    {
        $username = strtolower(trim($username));

        if (!$this->matches($username)) {
            throw new InvalidArgumentException('Username does not match organization pattern.');
        }
		
        return new LoginIdentifier(
            type: 'organization',
            subtype: 'candidate',
            id: $username,
            raw: $username
        );
    }
}
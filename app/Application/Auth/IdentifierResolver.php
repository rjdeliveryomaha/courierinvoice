<?php

declare(strict_types=1);

namespace CourierInvoice\Application\Auth;

use CourierInvoice\Domain\Auth\Matcher\ClientEmailMatcher;
use CourierInvoice\Domain\Auth\Matcher\ClientNonrepeatMatcher;
use CourierInvoice\Domain\Auth\Matcher\ClientNumericMatcher;
use CourierInvoice\Domain\Auth\Matcher\DriverMatcher;
use CourierInvoice\Domain\Auth\Matcher\DispatcherMatcher;
use CourierInvoice\Domain\Auth\Matcher\OrganizationMatcher;
use RuntimeException;

class IdentifierResolver
{
	/** @var IdentifierMatcherInterface[] */
    private array $matchers;

    public function __construct()
    {
        $this->matchers = [
            new ClientEmailMatcher(),
            new ClientNonrepeatMatcher(),
            new ClientNumericMatcher(),
            new DriverMatcher(),
            new DispatcherMatcher(),
            new OrganizationMatcher(),
        ];
    }

    public function resolve(string $username): object
    {
        $username = strtolower(trim($username));

        foreach ($this->matchers as $matcher) {
            if ($matcher->matches($username)) {
                return $matcher->resolve($username);
            }
        }

        throw new RuntimeException('Invalid credentials.');
    }
}
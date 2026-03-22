<?php

declare(strict_types=1);

namespace CourierInvoice\Application\Auth;

use CourierInvoice\Domain\Auth\LoginIdentifier;
use CourierInvoice\Domain\Auth\AuthenticatedIdentity;
use CourierInvoice\Domain\Auth\AuthenticatorInterface;

class LoginService
{
    public function __construct(
        private IdentifierResolver $resolver,
        private iterable $authenticators
    ) {}

    public function login(string $identifier, string $password): AuthResult
    {
        $loginIdentifier = $this->resolver->resolve($identifier);

        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->supports($loginIdentifier)) {
                return $authenticator->authenticate($loginIdentifier, $password);
            }
        }

        throw new \Exception('Invalid login credentials');
    }
}
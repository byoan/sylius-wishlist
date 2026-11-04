<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Context;

use Symfony\Component\HttpFoundation\RequestStack;

final readonly class SessionGuestTokenStorage implements GuestTokenStorageInterface
{
    public const SESSION_KEY = '_byoan_sylius_wishlist_token';

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function getToken(): ?string
    {
        if (!$this->hasReadableSession()) {
            return null;
        }

        $token = $this->requestStack->getSession()->get(self::SESSION_KEY);

        return \is_string($token) ? $token : null;
    }

    public function getOrCreateToken(): string
    {
        $token = $this->getToken();
        if (null !== $token) {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $this->requestStack->getSession()->set(self::SESSION_KEY, $token);

        return $token;
    }

    public function clear(): void
    {
        if ($this->hasReadableSession()) {
            $this->requestStack->getSession()->remove(self::SESSION_KEY);
        }
    }

    /** Avoids starting a session, and so sending a cookie, for visitors who have none yet. */
    private function hasReadableSession(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || !$request->hasSession()) {
            return false;
        }

        return $request->getSession()->isStarted() || $request->hasPreviousSession();
    }
}

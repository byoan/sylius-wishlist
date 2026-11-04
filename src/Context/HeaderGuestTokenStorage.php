<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Context;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The shop API is stateless, so a guest proves their identity by sending the token they were given
 * when they created their first wishlist. A malformed token counts as none, so a client can never
 * choose its own.
 */
final readonly class HeaderGuestTokenStorage implements GuestTokenStorageInterface
{
    public const HEADER = 'X-Wishlist-Token';

    private const NEW_TOKEN_ATTRIBUTE = '_byoan_sylius_wishlist_new_token';

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function getToken(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        $newToken = $request->attributes->get(self::NEW_TOKEN_ATTRIBUTE);
        if (\is_string($newToken)) {
            return $newToken;
        }

        $token = $request->headers->get(self::HEADER);

        return null !== $token && 1 === preg_match('/^[a-f0-9]{64}$/', $token) ? $token : null;
    }

    public function getOrCreateToken(): string
    {
        $token = $this->getToken();
        if (null !== $token) {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $this->requestStack->getCurrentRequest()?->attributes->set(self::NEW_TOKEN_ATTRIBUTE, $token);

        return $token;
    }

    public function clear(): void
    {
        $this->requestStack->getCurrentRequest()?->attributes->remove(self::NEW_TOKEN_ATTRIBUTE);
    }
}

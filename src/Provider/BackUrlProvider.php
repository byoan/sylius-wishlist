<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Provider;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Where to return after adding from a product page or listing. The resource controller's plain
 * referer redirect fails when the header is missing and follows it to any host.
 */
final readonly class BackUrlProvider
{
    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getUrl(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $referer = (string) $request?->headers->get('referer');

        if ('' !== $referer && parse_url($referer, \PHP_URL_HOST) === $request?->getHost()) {
            return $referer;
        }

        return $this->urlGenerator->generate('byoan_sylius_wishlist_shop_wishlist_show', [
            '_locale' => $request?->getLocale(),
        ]);
    }
}

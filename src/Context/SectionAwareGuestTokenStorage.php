<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Context;

use Sylius\Bundle\ApiBundle\SectionResolver\ShopApiSection;
use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;

/** Guests are identified by the session in the storefront and by a header in the shop API. */
final readonly class SectionAwareGuestTokenStorage implements GuestTokenStorageInterface
{
    public function __construct(
        private SectionProviderInterface $sectionProvider,
        private GuestTokenStorageInterface $sessionStorage,
        private GuestTokenStorageInterface $headerStorage,
    ) {
    }

    public function getToken(): ?string
    {
        return $this->storage()->getToken();
    }

    public function getOrCreateToken(): string
    {
        return $this->storage()->getOrCreateToken();
    }

    public function clear(): void
    {
        $this->storage()->clear();
    }

    private function storage(): GuestTokenStorageInterface
    {
        return $this->sectionProvider->getSection() instanceof ShopApiSection ? $this->headerStorage : $this->sessionStorage;
    }
}

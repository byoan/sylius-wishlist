<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Context;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Factory\DefaultWishlistFactoryInterface;
use Byoan\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Symfony\Contracts\Service\ResetInterface;

final class WishlistContext implements WishlistContextInterface, ResetInterface
{
    private ?WishlistInterface $wishlist = null;

    public function __construct(
        private readonly ChannelContextInterface $channelContext,
        private readonly CustomerContextInterface $customerContext,
        private readonly GuestTokenStorageInterface $guestTokenStorage,
        private readonly WishlistRepositoryInterface $wishlistRepository,
        private readonly DefaultWishlistFactoryInterface $defaultWishlistFactory,
    ) {
    }

    public function getWishlist(): WishlistInterface
    {
        return $this->wishlist ??= $this->resolveWishlist();
    }

    public function getWishlistForNewItem(): WishlistInterface
    {
        $wishlist = $this->getWishlist();

        if (null === $wishlist->getCustomer() && null === $wishlist->getToken()) {
            $wishlist->setToken($this->guestTokenStorage->getOrCreateToken());
        }

        return $wishlist;
    }

    public function reset(): void
    {
        $this->wishlist = null;
    }

    private function resolveWishlist(): WishlistInterface
    {
        $channel = $this->channelContext->getChannel();

        $customer = $this->customerContext->getCustomer();
        if (null !== $customer) {
            return $this->wishlistRepository->findDefaultByCustomerAndChannel($customer, $channel)
                ?? $this->defaultWishlistFactory->createForCustomer($channel, $customer);
        }

        $token = $this->guestTokenStorage->getToken();
        $wishlist = null !== $token ? $this->wishlistRepository->findDefaultByTokenAndChannel($token, $channel) : null;

        return $wishlist ?? $this->defaultWishlistFactory->createForGuest($channel, $token);
    }
}

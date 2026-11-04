<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Context;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Byoan\SyliusWishlistPlugin\Factory\DefaultWishlistFactoryInterface;
use Byoan\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Contracts\Service\ResetInterface;

final class WishlistContext implements WishlistContextInterface, ResetInterface
{
    /** @var list<WishlistInterface>|null */
    private ?array $wishlists = null;

    /** @param FactoryInterface<WishlistInterface> $wishlistFactory */
    public function __construct(
        private readonly ChannelContextInterface $channelContext,
        private readonly CustomerContextInterface $customerContext,
        private readonly GuestTokenStorageInterface $guestTokenStorage,
        private readonly WishlistRepositoryInterface $wishlistRepository,
        private readonly DefaultWishlistFactoryInterface $defaultWishlistFactory,
        private readonly FactoryInterface $wishlistFactory,
    ) {
    }

    public function getWishlists(): array
    {
        return $this->wishlists ??= $this->loadWishlists();
    }

    public function getWishlist(): WishlistInterface
    {
        return $this->getWishlists()[0];
    }

    public function getOwnedWishlist(mixed $id): ?WishlistInterface
    {
        foreach ($this->getWishlists() as $wishlist) {
            if (null !== $wishlist->getId() && (string) $wishlist->getId() === (string) $id) {
                return $wishlist;
            }
        }

        return null;
    }

    public function getOwnedItem(mixed $id): ?WishlistItemInterface
    {
        foreach ($this->getWishlists() as $wishlist) {
            foreach ($wishlist->getItems() as $item) {
                if ((string) $item->getId() === (string) $id) {
                    return $item;
                }
            }
        }

        return null;
    }

    public function getWishlistForNewItem(): WishlistInterface
    {
        $wishlist = $this->getWishlist();

        if (null === $wishlist->getCustomer() && null === $wishlist->getToken()) {
            $wishlist->setToken($this->guestTokenStorage->getOrCreateToken());
        }

        return $wishlist;
    }

    public function createWishlist(): WishlistInterface
    {
        $default = $this->getWishlistForNewItem();
        if (null === $default->getId()) {
            $this->wishlistRepository->add($default);
        }

        $wishlist = $this->wishlistFactory->createNew();
        $wishlist->setChannel($default->getChannel());
        $wishlist->setCustomer($default->getCustomer());
        $wishlist->setToken($default->getToken());

        return $wishlist;
    }

    public function reset(): void
    {
        $this->wishlists = null;
    }

    /** @return list<WishlistInterface> */
    private function loadWishlists(): array
    {
        $channel = $this->channelContext->getChannel();
        $customer = $this->customerContext->getCustomer();
        $token = null === $customer ? $this->guestTokenStorage->getToken() : null;

        $wishlists = [];
        if (null !== $customer) {
            $wishlists = $this->wishlistRepository->findByCustomerAndChannel($customer, $channel);
        } elseif (null !== $token) {
            $wishlists = $this->wishlistRepository->findByTokenAndChannel($token, $channel);
        }

        $default = null;
        $others = [];
        foreach ($wishlists as $wishlist) {
            if ($wishlist->isDefault()) {
                $default = $wishlist;
            } else {
                $others[] = $wishlist;
            }
        }

        if (null === $default) {
            $default = null !== $customer
                ? $this->defaultWishlistFactory->createForCustomer($channel, $customer)
                : $this->defaultWishlistFactory->createForGuest($channel, $token);
        }

        return [$default, ...$others];
    }
}

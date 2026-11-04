<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Transfer;

use Byoan\SyliusWishlistPlugin\Context\GuestTokenStorageInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Byoan\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Resource\Factory\FactoryInterface;

final readonly class GuestWishlistTransfer implements GuestWishlistTransferInterface
{
    /** @param FactoryInterface<WishlistItemInterface> $wishlistItemFactory */
    public function __construct(
        private GuestTokenStorageInterface $guestTokenStorage,
        private WishlistRepositoryInterface $wishlistRepository,
        private FactoryInterface $wishlistItemFactory,
    ) {
    }

    public function transferTo(CustomerInterface $customer): void
    {
        $token = $this->guestTokenStorage->getToken();
        if (null === $token) {
            return;
        }

        foreach ($this->wishlistRepository->findByToken($token) as $wishlist) {
            $existing = $this->findMatchingCustomerWishlist($customer, $wishlist);

            if (null === $existing) {
                $wishlist->setCustomer($customer);
                $wishlist->setToken(null);
                $this->wishlistRepository->add($wishlist);

                continue;
            }

            $this->mergeItems($wishlist, $existing);
            $this->wishlistRepository->add($existing);
            $this->wishlistRepository->remove($wishlist);
        }

        // The shop firewall keeps the session on logout, so the token must not outlive the transfer.
        $this->guestTokenStorage->clear();
    }

    /** A customer's default wishlist, or the one of the same name, is merged into rather than duplicated. */
    private function findMatchingCustomerWishlist(CustomerInterface $customer, WishlistInterface $wishlist): ?WishlistInterface
    {
        $criteria = ['customer' => $customer, 'channel' => $wishlist->getChannel()];
        $criteria += $wishlist->isDefault() ? ['default' => true] : ['name' => $wishlist->getName()];

        return $this->wishlistRepository->findOneBy($criteria);
    }

    private function mergeItems(WishlistInterface $from, WishlistInterface $to): void
    {
        foreach ($from->getItems() as $item) {
            $variant = $item->getVariant();
            if (null === $variant || $to->hasVariant($variant)) {
                continue;
            }

            $copy = $this->wishlistItemFactory->createNew();
            $copy->setVariant($variant);
            $to->addItem($copy);
        }
    }
}

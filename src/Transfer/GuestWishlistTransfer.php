<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Transfer;

use Byoan\SyliusWishlistPlugin\Context\GuestTokenStorageInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Byoan\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

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
            $existing = $this->findCustomerDefaultWishlist($customer, $wishlist);

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

    /** A customer can only have one default wishlist per channel, so a second one is merged. */
    private function findCustomerDefaultWishlist(CustomerInterface $customer, WishlistInterface $wishlist): ?WishlistInterface
    {
        $channel = $wishlist->getChannel();
        Assert::notNull($channel);

        return $wishlist->isDefault()
            ? $this->wishlistRepository->findDefaultByCustomerAndChannel($customer, $channel)
            : null;
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

<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Mover;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class WishlistItemMover implements WishlistItemMoverInterface
{
    /** @param RepositoryInterface<WishlistItemInterface> $wishlistItemRepository */
    public function __construct(private RepositoryInterface $wishlistItemRepository)
    {
    }

    public function move(WishlistItemInterface $item, WishlistInterface $target): WishlistItemInterface
    {
        $variant = $item->getVariant();
        Assert::notNull($variant);

        $existing = $target->getItemForVariant($variant);
        if (null !== $existing && $existing !== $item) {
            $this->wishlistItemRepository->remove($item);

            return $existing;
        }

        // Only the item's own side changes: removing it from the source collection would delete it.
        $item->setWishlist($target);
        $target->setUpdatedAt(new \DateTimeImmutable());
        $this->wishlistItemRepository->add($item);

        return $item;
    }
}

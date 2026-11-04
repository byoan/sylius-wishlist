<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Context;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;

/**
 * The current shopper's wishlists in the current channel, whether customer or guest. Everything the
 * shop routes reach goes through here, so a shopper can only ever reach their own wishlists.
 */
interface WishlistContextInterface
{
    /**
     * Default wishlist first. It is unsaved and empty when the shopper has none yet, so reads never write.
     *
     * @return list<WishlistInterface>
     */
    public function getWishlists(): array;

    public function getWishlist(): WishlistInterface;

    public function getOwnedWishlist(mixed $id): ?WishlistInterface;

    public function getOwnedItem(mixed $id): ?WishlistItemInterface;

    /** Same as getWishlist(), but makes sure a guest has a token the wishlist can be saved under. */
    public function getWishlistForNewItem(): WishlistInterface;

    /**
     * Creates a new wishlist for the shopper. The default one is saved first, so that the new
     * wishlist's name is validated against it.
     */
    public function createWishlist(): WishlistInterface;
}

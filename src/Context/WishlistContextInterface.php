<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Context;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;

/** The current shopper's default wishlist in the current channel, whether customer or guest. */
interface WishlistContextInterface
{
    /** Returns an unsaved empty wishlist when the shopper has none yet, so reads never write. */
    public function getWishlist(): WishlistInterface;

    /** Same as getWishlist(), but makes sure a guest has a token the wishlist can be saved under. */
    public function getWishlistForNewItem(): WishlistInterface;
}

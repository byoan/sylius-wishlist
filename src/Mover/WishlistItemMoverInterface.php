<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Mover;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;

interface WishlistItemMoverInterface
{
    /**
     * Moves the item to the target wishlist. If the target already holds that product, the moved
     * entry is dropped, so the product still ends up only in the target.
     *
     * @return WishlistItemInterface the item now holding the product in the target wishlist
     */
    public function move(WishlistItemInterface $item, WishlistInterface $target): WishlistItemInterface;
}

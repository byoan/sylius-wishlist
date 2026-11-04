<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Factory;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Factory\FactoryInterface;

/** @extends FactoryInterface<WishlistItemInterface> */
interface WishlistItemFactoryInterface extends FactoryInterface
{
    /** Creates an item for the given wishlist, or for the shopper's default one. */
    public function createForVariant(ProductVariantInterface $variant, ?WishlistInterface $wishlist = null): WishlistItemInterface;
}

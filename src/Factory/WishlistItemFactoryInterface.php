<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Factory;

use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Factory\FactoryInterface;

/** @extends FactoryInterface<WishlistItemInterface> */
interface WishlistItemFactoryInterface extends FactoryInterface
{
    /** Creates an item for the current shopper's wishlist. */
    public function createForVariant(ProductVariantInterface $variant): WishlistItemInterface;
}

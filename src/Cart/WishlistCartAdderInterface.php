<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Cart;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface WishlistCartAdderInterface
{
    /**
     * Adds one unit of each variant to the current cart. A rejected variant does not stop the
     * others from being added.
     *
     * @param iterable<ProductVariantInterface> $variants
     */
    public function add(iterable $variants, ChannelInterface $channel): AddToCartResult;
}

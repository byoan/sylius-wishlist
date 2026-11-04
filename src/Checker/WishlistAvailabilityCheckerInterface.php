<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Checker;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface WishlistAvailabilityCheckerInterface
{
    /** Whether the product page exists: it returns a 404 for a disabled product or another channel's product. */
    public function isProductVisible(ProductVariantInterface $variant, ChannelInterface $channel): bool;

    public function hasPrice(ProductVariantInterface $variant, ChannelInterface $channel): bool;

    /** A variant can be saved while out of stock, but not while it cannot be sold at all in the channel. */
    public function canBeWishlisted(ProductVariantInterface $variant, ChannelInterface $channel): bool;

    /** Whether the variant can currently be added to the cart. */
    public function isAvailable(ProductVariantInterface $variant, ChannelInterface $channel): bool;
}

<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Checker;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;

final readonly class WishlistAvailabilityChecker implements WishlistAvailabilityCheckerInterface
{
    public function __construct(private AvailabilityCheckerInterface $availabilityChecker)
    {
    }

    public function isProductVisible(ProductVariantInterface $variant, ChannelInterface $channel): bool
    {
        $product = $variant->getProduct();

        return $product instanceof ProductInterface && $product->isEnabled() && $product->hasChannel($channel);
    }

    public function hasPrice(ProductVariantInterface $variant, ChannelInterface $channel): bool
    {
        // The price calculator throws when the channel has no price, so this must be checked first.
        return null !== $variant->getChannelPricingForChannel($channel)?->getPrice();
    }

    public function canBeWishlisted(ProductVariantInterface $variant, ChannelInterface $channel): bool
    {
        return $variant->isEnabled() && $this->isProductVisible($variant, $channel);
    }

    public function isAvailable(ProductVariantInterface $variant, ChannelInterface $channel): bool
    {
        return $this->canBeWishlisted($variant, $channel) &&
            $this->hasPrice($variant, $channel) &&
            $this->availabilityChecker->isStockAvailable($variant);
    }
}

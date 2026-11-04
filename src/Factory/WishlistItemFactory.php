<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Factory;

use Byoan\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Factory\FactoryInterface;

final readonly class WishlistItemFactory implements WishlistItemFactoryInterface
{
    /** @param FactoryInterface<WishlistItemInterface> $decoratedFactory */
    public function __construct(
        private FactoryInterface $decoratedFactory,
        private WishlistContextInterface $wishlistContext,
    ) {
    }

    public function createNew(): WishlistItemInterface
    {
        return $this->decoratedFactory->createNew();
    }

    public function createForVariant(ProductVariantInterface $variant): WishlistItemInterface
    {
        $item = $this->createNew();
        $item->setVariant($variant);
        $item->setWishlist($this->wishlistContext->getWishlistForNewItem());

        return $item;
    }
}

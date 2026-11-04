<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Cart;

use Sylius\Component\Core\Model\ProductVariantInterface;

final readonly class AddToCartResult
{
    /**
     * @param list<ProductVariantInterface> $added
     * @param list<ProductVariantInterface> $rejected
     */
    public function __construct(
        public array $added,
        public array $rejected,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class WishlistExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('byoan_sylius_wishlist_count', [WishlistRuntime::class, 'count']),
            new TwigFunction('byoan_sylius_wishlist_contains', [WishlistRuntime::class, 'contains']),
            new TwigFunction('byoan_sylius_wishlist_item_form', [WishlistRuntime::class, 'itemForm']),
            new TwigFunction('byoan_sylius_wishlist_selection_form', [WishlistRuntime::class, 'selectionForm']),
            new TwigFunction('byoan_sylius_wishlist_listing_variant', [WishlistRuntime::class, 'listingVariant']),
            new TwigFunction('byoan_sylius_wishlist_is_available', [WishlistRuntime::class, 'isAvailable']),
            new TwigFunction('byoan_sylius_wishlist_is_product_visible', [WishlistRuntime::class, 'isProductVisible']),
            new TwigFunction('byoan_sylius_wishlist_has_price', [WishlistRuntime::class, 'hasPrice']),
        ];
    }
}

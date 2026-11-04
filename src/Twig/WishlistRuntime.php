<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Twig;

use Byoan\SyliusWishlistPlugin\Checker\WishlistAvailabilityCheckerInterface;
use Byoan\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Byoan\SyliusWishlistPlugin\Form\Factory\WishlistFormFactoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class WishlistRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private WishlistContextInterface $wishlistContext,
        private WishlistFormFactoryInterface $formFactory,
        private WishlistAvailabilityCheckerInterface $availabilityChecker,
        private ProductVariantResolverInterface $productVariantResolver,
        private ChannelContextInterface $channelContext,
    ) {
    }

    /** @return list<WishlistInterface> */
    public function lists(): array
    {
        return $this->wishlistContext->getWishlists();
    }

    /** @return list<WishlistInterface> */
    public function otherLists(): array
    {
        return array_values(array_filter(
            $this->wishlistContext->getWishlists(),
            static fn (WishlistInterface $wishlist): bool => !$wishlist->isDefault(),
        ));
    }

    public function createForm(): FormView
    {
        return $this->formFactory->createWishlistForm()->createView();
    }

    public function setDefaultForm(WishlistInterface $wishlist): FormView
    {
        return $this->formFactory->createSetDefaultForm($wishlist)->createView();
    }

    public function moveForm(WishlistItemInterface $item): FormView
    {
        return $this->formFactory->createMoveForm($item)->createView();
    }

    public function count(): int
    {
        return array_sum(array_map(
            static fn (WishlistInterface $wishlist): int => $wishlist->getItems()->count(),
            $this->wishlistContext->getWishlists(),
        ));
    }

    public function contains(ProductVariantInterface $variant): bool
    {
        return $this->wishlistContext->getWishlist()->hasVariant($variant);
    }

    /** @param array<string, mixed> $routeParameters */
    public function itemForm(string $route, array $routeParameters = []): FormView
    {
        return $this->formFactory->createItemForm($route, $routeParameters)->createView();
    }

    public function selectionForm(WishlistInterface $wishlist): FormView
    {
        return $this->formFactory->createSelectionForm($wishlist, $this->channel())->createView();
    }

    /**
     * Listings have no variant picker. The default resolver ignores stock, so when its variant
     * cannot be bought this prefers another one that can.
     */
    public function listingVariant(ProductInterface $product): ?ProductVariantInterface
    {
        /** @var ProductVariantInterface|null $resolved */
        $resolved = $this->productVariantResolver->getVariant($product);
        if (null === $resolved) {
            return null;
        }

        $channel = $this->channel();
        if ($this->availabilityChecker->isAvailable($resolved, $channel)) {
            return $resolved;
        }

        foreach ($product->getEnabledVariants() as $variant) {
            if ($variant instanceof ProductVariantInterface && $this->availabilityChecker->isAvailable($variant, $channel)) {
                return $variant;
            }
        }

        return $resolved;
    }

    public function isAvailable(ProductVariantInterface $variant, ?ChannelInterface $channel = null): bool
    {
        return $this->availabilityChecker->isAvailable($variant, $channel ?? $this->channel());
    }

    public function isProductVisible(ProductVariantInterface $variant, ?ChannelInterface $channel = null): bool
    {
        return $this->availabilityChecker->isProductVisible($variant, $channel ?? $this->channel());
    }

    public function hasPrice(ProductVariantInterface $variant, ?ChannelInterface $channel = null): bool
    {
        return $this->availabilityChecker->hasPrice($variant, $channel ?? $this->channel());
    }

    private function channel(): ChannelInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();

        return $channel;
    }
}

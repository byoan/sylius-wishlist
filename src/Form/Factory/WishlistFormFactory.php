<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Form\Factory;

use Byoan\SyliusWishlistPlugin\Checker\WishlistAvailabilityCheckerInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Byoan\SyliusWishlistPlugin\Form\Type\WishlistItemMoveType;
use Byoan\SyliusWishlistPlugin\Form\Type\WishlistItemType;
use Byoan\SyliusWishlistPlugin\Form\Type\WishlistSelectionType;
use Byoan\SyliusWishlistPlugin\Form\Type\WishlistSetDefaultType;
use Byoan\SyliusWishlistPlugin\Form\Type\WishlistType;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class WishlistFormFactory implements WishlistFormFactoryInterface
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private LocaleContextInterface $localeContext,
        private WishlistAvailabilityCheckerInterface $availabilityChecker,
    ) {
    }

    public function createItemForm(string $route, array $routeParameters = []): FormInterface
    {
        return $this->formFactory->create(WishlistItemType::class, null, [
            'action' => $this->generateUrl($route, $routeParameters),
        ]);
    }

    public function createWishlistForm(): FormInterface
    {
        return $this->formFactory->create(WishlistType::class, null, [
            'action' => $this->generateUrl('byoan_sylius_wishlist_shop_wishlist_create'),
        ]);
    }

    public function createSetDefaultForm(WishlistInterface $wishlist): FormInterface
    {
        return $this->formFactory->create(WishlistSetDefaultType::class, $wishlist, [
            'action' => $this->generateUrl('byoan_sylius_wishlist_shop_wishlist_set_as_default', ['id' => $wishlist->getId()]),
            'method' => 'PATCH',
        ]);
    }

    public function createMoveForm(WishlistItemInterface $item): FormInterface
    {
        return $this->formFactory->create(WishlistItemMoveType::class, $item, [
            'action' => $this->generateUrl('byoan_sylius_wishlist_shop_wishlist_item_move', ['id' => $item->getId()]),
            'method' => 'PUT',
        ]);
    }

    public function createSelectionForm(WishlistInterface $wishlist, ChannelInterface $channel): FormInterface
    {
        $codes = [];
        foreach ($wishlist->getItems() as $item) {
            $variant = $item->getVariant();
            if (null !== $variant && $this->availabilityChecker->isAvailable($variant, $channel)) {
                $codes[] = (string) $variant->getCode();
            }
        }

        return $this->formFactory->create(WishlistSelectionType::class, null, [
            'variant_codes' => $codes,
            'action' => $this->generateUrl('byoan_sylius_wishlist_shop_wishlist_add_selected_to_cart', ['id' => $wishlist->getId()]),
        ]);
    }

    /**
     * The locale is explicit because the product page re-renders these forms in a live component
     * request, which is outside the shop's localized routes.
     *
     * @param array<string, mixed> $parameters
     */
    private function generateUrl(string $route, array $parameters = []): string
    {
        return $this->urlGenerator->generate($route, $parameters + ['_locale' => $this->localeContext->getLocaleCode()]);
    }
}

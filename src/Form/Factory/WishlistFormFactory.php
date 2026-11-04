<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Form\Factory;

use Byoan\SyliusWishlistPlugin\Checker\WishlistAvailabilityCheckerInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Form\Type\WishlistItemType;
use Byoan\SyliusWishlistPlugin\Form\Type\WishlistSelectionType;
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
            'action' => $this->generateUrl('byoan_sylius_wishlist_shop_wishlist_add_selected_to_cart'),
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

<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Controller\Shop;

use Byoan\SyliusWishlistPlugin\Cart\WishlistCartAdderInterface;
use Byoan\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Byoan\SyliusWishlistPlugin\Form\Factory\WishlistFormFactoryInterface;
use Byoan\SyliusWishlistPlugin\Provider\BackUrlProvider;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The one wishlist action the resource controller cannot do: it acts on a selection rather than a
 * single resource. Everything else goes through the routes in config/routes/shop.yaml.
 */
final readonly class AddSelectedItemsToCartAction
{
    public function __construct(
        private WishlistContextInterface $wishlistContext,
        private WishlistCartAdderInterface $cartAdder,
        private WishlistFormFactoryInterface $formFactory,
        private ChannelContextInterface $channelContext,
        private BackUrlProvider $backUrlProvider,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $wishlist = $this->wishlistContext->getOwnedWishlist($id);
        if (null === $wishlist) {
            throw new NotFoundHttpException(\sprintf('You have no wishlist with id "%s".', $id));
        }

        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();

        // The choices are the wishlist's available variants, so anything else fails validation.
        $form = $this->formFactory->createSelectionForm($wishlist, $channel)->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->redirectWithFlash($request, 'error', 'byoan_sylius_wishlist.selection_invalid');
        }

        /** @var list<string> $selectedCodes */
        $selectedCodes = $form->get('variants')->getData() ?? [];
        if ([] === $selectedCodes) {
            return $this->redirectWithFlash($request, 'info', 'byoan_sylius_wishlist.nothing_selected');
        }

        $variants = [];
        foreach ($wishlist->getItems() as $item) {
            $variant = $item->getVariant();
            if (null !== $variant && \in_array($variant->getCode(), $selectedCodes, true)) {
                $variants[] = $variant;
            }
        }

        $result = $this->cartAdder->add($variants, $channel);

        if ([] !== $result->added) {
            $this->flash($request, 'success', 'byoan_sylius_wishlist.items_added_to_cart', ['%names%' => $this->names($result->added)]);
        }
        if ([] !== $result->rejected) {
            $this->flash($request, 'error', 'byoan_sylius_wishlist.items_not_added_to_cart', ['%names%' => $this->names($result->rejected)]);
        }

        return new RedirectResponse($this->backUrlProvider->getUrl());
    }

    private function redirectWithFlash(Request $request, string $type, string $message): RedirectResponse
    {
        $this->flash($request, $type, $message);

        return new RedirectResponse($this->backUrlProvider->getUrl());
    }

    /** @param array<string, string> $parameters */
    private function flash(Request $request, string $type, string $message, array $parameters = []): void
    {
        $session = $request->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add($type, ['message' => $message, 'parameters' => $parameters]);
        }
    }

    /** @param list<ProductVariantInterface> $variants */
    private function names(array $variants): string
    {
        return implode(', ', array_map(
            static fn (ProductVariantInterface $variant): string => (string) ($variant->getProduct()?->getName() ?? $variant->getCode()),
            $variants,
        ));
    }
}

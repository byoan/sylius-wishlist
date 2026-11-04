<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\EventListener;

use Byoan\SyliusWishlistPlugin\Cart\WishlistCartAdderInterface;
use Byoan\SyliusWishlistPlugin\Checker\WishlistAvailabilityCheckerInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Symfony\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

/** Business rules around the resource controller's wishlist item actions. */
final readonly class WishlistItemListener
{
    public function __construct(
        private WishlistAvailabilityCheckerInterface $availabilityChecker,
        private WishlistCartAdderInterface $cartAdder,
        private ChannelContextInterface $channelContext,
    ) {
    }

    public function preCreate(ResourceControllerEvent $event): void
    {
        $item = $this->getItem($event);
        $variant = $this->getVariant($item);
        $wishlist = $item->getWishlist();
        Assert::notNull($wishlist);

        if (!$this->availabilityChecker->canBeWishlisted($variant, $this->getChannel())) {
            $event->stop('byoan_sylius_wishlist.item_not_addable', GenericEvent::TYPE_ERROR, $this->getMessageParameters($variant));

            return;
        }

        if ($wishlist->hasVariant($variant)) {
            $event->stop('byoan_sylius_wishlist.item_already_added', GenericEvent::TYPE_INFO, $this->getMessageParameters($variant));

            return;
        }

        $wishlist->addItem($item);
    }

    public function preDelete(ResourceControllerEvent $event): void
    {
        $item = $this->getItem($event);

        $item->getWishlist()?->removeItem($item);
    }

    public function preAddToCart(ResourceControllerEvent $event): void
    {
        $variant = $this->getVariant($this->getItem($event));

        if ([] === $this->cartAdder->add([$variant], $this->getChannel())->added) {
            $event->stop('byoan_sylius_wishlist.item_not_added_to_cart', GenericEvent::TYPE_ERROR, $this->getMessageParameters($variant));
        }
    }

    private function getItem(ResourceControllerEvent $event): WishlistItemInterface
    {
        $item = $event->getSubject();
        Assert::isInstanceOf($item, WishlistItemInterface::class);

        return $item;
    }

    private function getVariant(WishlistItemInterface $item): ProductVariantInterface
    {
        $variant = $item->getVariant();
        Assert::notNull($variant);

        return $variant;
    }

    private function getChannel(): ChannelInterface
    {
        $channel = $this->channelContext->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        return $channel;
    }

    /** @return array<string, string> */
    private function getMessageParameters(ProductVariantInterface $variant): array
    {
        return ['%name%' => (string) ($variant->getProduct()?->getName() ?? $variant->getCode())];
    }
}

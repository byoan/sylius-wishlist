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
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Symfony\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

/** Business rules around the resource controller's wishlist item actions. */
final readonly class WishlistItemListener
{
    /** @param RepositoryInterface<WishlistItemInterface> $wishlistItemRepository */
    public function __construct(
        private WishlistAvailabilityCheckerInterface $availabilityChecker,
        private WishlistCartAdderInterface $cartAdder,
        private ChannelContextInterface $channelContext,
        private RepositoryInterface $wishlistItemRepository,
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

    /**
     * The form has already set the target wishlist on the item. If the target holds the product
     * already, the source entry is dropped: the product still ends up only in the target.
     */
    public function preMove(ResourceControllerEvent $event): void
    {
        $item = $this->getItem($event);
        $target = $item->getWishlist();
        Assert::notNull($target);

        if ($target->hasVariant($this->getVariant($item))) {
            $this->wishlistItemRepository->remove($item);
            $event->stop('byoan_sylius_wishlist.item_moved_already_present', GenericEvent::TYPE_SUCCESS);

            return;
        }

        $target->setUpdatedAt(new \DateTimeImmutable());
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

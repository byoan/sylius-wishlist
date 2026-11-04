<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\EventListener;

use Byoan\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Resource\Symfony\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final readonly class WishlistListener
{
    public function __construct(
        private WishlistContextInterface $wishlistContext,
        private WishlistRepositoryInterface $wishlistRepository,
    ) {
    }

    /** The default wishlist is the add button's target, so it always has to exist. */
    public function preDelete(ResourceControllerEvent $event): void
    {
        if ($this->getWishlist($event)->isDefault()) {
            $event->stop('byoan_sylius_wishlist.wishlist_not_deletable', GenericEvent::TYPE_ERROR);
        }
    }

    /**
     * The old default is saved first: the unique index allows only one default per owner and
     * channel, and a single flush does not guarantee the order of the two updates. The resource
     * controller then saves the new default.
     */
    public function preSetAsDefault(ResourceControllerEvent $event): void
    {
        $wishlist = $this->getWishlist($event);
        if ($wishlist->isDefault()) {
            return;
        }

        $current = $this->wishlistContext->getWishlist();
        $current->setDefault(false);
        $this->wishlistRepository->add($current);

        $wishlist->setDefault(true);
    }

    private function getWishlist(ResourceControllerEvent $event): WishlistInterface
    {
        $wishlist = $event->getSubject();
        Assert::isInstanceOf($wishlist, WishlistInterface::class);

        return $wishlist;
    }
}

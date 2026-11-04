<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function __invoke(MenuBuilderEvent $event): void
    {
        $event->getMenu()->getChild('customers')
            ?->addChild('wishlists', [
                'route' => 'byoan_sylius_wishlist_admin_wishlist_index',
                'extras' => ['routes' => [['route' => 'byoan_sylius_wishlist_admin_wishlist_show']]],
            ])
            ->setLabel('byoan_sylius_wishlist.ui.wishlists')
        ;
    }
}

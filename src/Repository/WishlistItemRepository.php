<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Repository;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class WishlistItemRepository extends EntityRepository implements WishlistItemRepositoryInterface
{
    public function findOneByIdAndWishlist(mixed $id, WishlistInterface $wishlist): ?WishlistItemInterface
    {
        // An unsaved wishlist has no items, and Doctrine cannot bind it as a query parameter.
        if (null === $wishlist->getId()) {
            return null;
        }

        return $this->findOneBy(['id' => $id, 'wishlist' => $wishlist]);
    }
}

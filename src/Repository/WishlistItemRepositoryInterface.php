<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Repository;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

/** @extends RepositoryInterface<WishlistItemInterface> */
interface WishlistItemRepositoryInterface extends RepositoryInterface
{
    public function findOneByIdAndWishlist(mixed $id, WishlistInterface $wishlist): ?WishlistItemInterface;
}

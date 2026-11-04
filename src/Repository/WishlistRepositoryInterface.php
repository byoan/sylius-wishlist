<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Repository;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

/** @extends RepositoryInterface<WishlistInterface> */
interface WishlistRepositoryInterface extends RepositoryInterface
{
    public function findDefaultByCustomerAndChannel(CustomerInterface $customer, ChannelInterface $channel): ?WishlistInterface;

    public function findDefaultByTokenAndChannel(string $token, ChannelInterface $channel): ?WishlistInterface;

    /** @return list<WishlistInterface> */
    public function findByToken(string $token): array;

    public function createCustomerListQueryBuilder(): QueryBuilder;

    public function findOneCustomerWishlist(mixed $id): ?WishlistInterface;
}

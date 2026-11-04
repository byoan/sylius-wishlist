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
    /** @return list<WishlistInterface> */
    public function findByCustomerAndChannel(CustomerInterface $customer, ChannelInterface $channel): array;

    /** @return list<WishlistInterface> */
    public function findByTokenAndChannel(string $token, ChannelInterface $channel): array;

    /** @return list<WishlistInterface> */
    public function findByToken(string $token): array;

    public function createAdminListQueryBuilder(): QueryBuilder;
}

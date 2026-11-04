<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Repository;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;

class WishlistRepository extends EntityRepository implements WishlistRepositoryInterface
{
    public function findDefaultByCustomerAndChannel(CustomerInterface $customer, ChannelInterface $channel): ?WishlistInterface
    {
        return $this->createDefaultWithItemsQueryBuilder($channel)
            ->andWhere('o.customer = :customer')
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findDefaultByTokenAndChannel(string $token, ChannelInterface $channel): ?WishlistInterface
    {
        return $this->createDefaultWithItemsQueryBuilder($channel)
            ->andWhere('o.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByToken(string $token): array
    {
        return $this->findBy(['token' => $token]);
    }

    public function createCustomerListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o')->andWhere('o.customer IS NOT NULL');
    }

    public function findOneCustomerWishlist(mixed $id): ?WishlistInterface
    {
        return $this->createCustomerListQueryBuilder()
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createDefaultWithItemsQueryBuilder(ChannelInterface $channel): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->addSelect('item', 'variant', 'product')
            ->leftJoin('o.items', 'item')
            ->leftJoin('item.variant', 'variant')
            ->leftJoin('variant.product', 'product')
            ->andWhere('o.channel = :channel')
            ->andWhere('o.default = true')
            ->setParameter('channel', $channel)
        ;
    }
}

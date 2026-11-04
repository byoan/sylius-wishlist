<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;

class WishlistRepository extends EntityRepository implements WishlistRepositoryInterface
{
    public function findByCustomerAndChannel(CustomerInterface $customer, ChannelInterface $channel): array
    {
        return $this->createWithItemsQueryBuilder($channel)
            ->andWhere('o.customer = :customer')
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByTokenAndChannel(string $token, ChannelInterface $channel): array
    {
        return $this->createWithItemsQueryBuilder($channel)
            ->andWhere('o.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByToken(string $token): array
    {
        return $this->findBy(['token' => $token]);
    }

    /**
     * The left join keeps guest wishlists listed: when sorting or filtering on a customer field, the
     * grid reuses an existing join but would otherwise add an inner one.
     */
    public function createAdminListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->addSelect('customer')
            ->leftJoin('o.customer', 'customer')
        ;
    }

    private function createWithItemsQueryBuilder(ChannelInterface $channel): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->addSelect('item', 'variant', 'product')
            ->leftJoin('o.items', 'item')
            ->leftJoin('item.variant', 'variant')
            ->leftJoin('variant.product', 'product')
            ->andWhere('o.channel = :channel')
            ->setParameter('channel', $channel)
            ->addOrderBy('o.createdAt', 'ASC')
            ->addOrderBy('o.id', 'ASC')
        ;
    }
}

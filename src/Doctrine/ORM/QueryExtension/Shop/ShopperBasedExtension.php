<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Doctrine\ORM\QueryExtension\Shop;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Byoan\SyliusWishlistPlugin\Context\GuestTokenStorageInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ApiBundle\SectionResolver\ShopApiSection;
use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;

/** Limits the shop API to the current shopper's wishlists in the current channel, so anything else is a 404. */
final readonly class ShopperBasedExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private SectionProviderInterface $sectionProvider,
        private ChannelContextInterface $channelContext,
        private CustomerContextInterface $customerContext,
        private GuestTokenStorageInterface $guestTokenStorage,
    ) {
    }

    /** @param array<array-key, mixed> $context */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->applyCondition($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    /**
     * @param array<array-key, mixed> $identifiers
     * @param array<array-key, mixed> $context
     */
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->applyCondition($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    private function applyCondition(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): void
    {
        if (!$this->sectionProvider->getSection() instanceof ShopApiSection) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        if (is_a($resourceClass, WishlistItemInterface::class, true)) {
            $wishlistAlias = $queryNameGenerator->generateJoinAlias('wishlist');
            $queryBuilder->innerJoin(\sprintf('%s.wishlist', $alias), $wishlistAlias);
            $alias = $wishlistAlias;
        } elseif (!is_a($resourceClass, WishlistInterface::class, true)) {
            return;
        }

        $channelParameter = $queryNameGenerator->generateParameterName('channel');
        $queryBuilder
            ->andWhere(\sprintf('%s.channel = :%s', $alias, $channelParameter))
            ->setParameter($channelParameter, $this->channelContext->getChannel())
        ;

        $customer = $this->customerContext->getCustomer();
        if (null !== $customer) {
            $this->restrictTo($queryBuilder, $queryNameGenerator, $alias, 'customer', $customer);

            return;
        }

        $token = $this->guestTokenStorage->getToken();
        if (null !== $token) {
            $this->restrictTo($queryBuilder, $queryNameGenerator, $alias, 'token', $token);

            return;
        }

        $queryBuilder->andWhere('1 = 0');
    }

    private function restrictTo(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $alias, string $field, mixed $value): void
    {
        $parameter = $queryNameGenerator->generateParameterName($field);

        $queryBuilder
            ->andWhere(\sprintf('%s.%s = :%s', $alias, $field, $parameter))
            ->setParameter($parameter, $value)
        ;
    }
}

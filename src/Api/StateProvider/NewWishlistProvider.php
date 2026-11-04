<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Api\StateProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Byoan\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;

/**
 * Gives a new wishlist its owner and channel before the request body is applied and validated, so
 * that the unique name check applies. The client never chooses the owner.
 *
 * @implements ProviderInterface<WishlistInterface>
 */
final readonly class NewWishlistProvider implements ProviderInterface
{
    public function __construct(private WishlistContextInterface $wishlistContext)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): WishlistInterface
    {
        return $this->wishlistContext->createWishlist();
    }
}

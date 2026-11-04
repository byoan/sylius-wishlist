<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Api\StateProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Byoan\SyliusWishlistPlugin\Api\Input\MoveWishlistItem;
use Byoan\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Byoan\SyliusWishlistPlugin\Mover\WishlistItemMoverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/** @implements ProcessorInterface<MoveWishlistItem, WishlistItemInterface> */
final readonly class MoveWishlistItemProcessor implements ProcessorInterface
{
    public function __construct(
        private WishlistContextInterface $wishlistContext,
        private WishlistItemMoverInterface $wishlistItemMover,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): WishlistItemInterface
    {
        $item = $this->wishlistContext->getOwnedItem($uriVariables['id'] ?? null);
        if (null === $item || (string) $item->getWishlist()?->getId() !== (string) ($uriVariables['wishlistId'] ?? null)) {
            throw new NotFoundHttpException('Wishlist item not found.');
        }

        $target = $this->wishlistContext->getOwnedWishlist($data->targetWishlistId);
        if (null === $target) {
            throw new UnprocessableEntityHttpException('The target wishlist does not exist.');
        }

        return $this->wishlistItemMover->move($item, $target);
    }
}

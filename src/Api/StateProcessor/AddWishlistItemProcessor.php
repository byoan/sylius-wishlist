<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Api\StateProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Byoan\SyliusWishlistPlugin\Api\Input\AddWishlistItem;
use Byoan\SyliusWishlistPlugin\Checker\WishlistAvailabilityCheckerInterface;
use Byoan\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Byoan\SyliusWishlistPlugin\Factory\WishlistItemFactoryInterface;
use Byoan\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/** @implements ProcessorInterface<AddWishlistItem, WishlistItemInterface> */
final readonly class AddWishlistItemProcessor implements ProcessorInterface
{
    /** @param ProductVariantRepositoryInterface<ProductVariantInterface> $productVariantRepository */
    public function __construct(
        private WishlistContextInterface $wishlistContext,
        private WishlistItemFactoryInterface $wishlistItemFactory,
        private WishlistRepositoryInterface $wishlistRepository,
        private ProductVariantRepositoryInterface $productVariantRepository,
        private WishlistAvailabilityCheckerInterface $availabilityChecker,
        private ChannelContextInterface $channelContext,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): WishlistItemInterface
    {
        $wishlist = $this->wishlistContext->getOwnedWishlist($uriVariables['id'] ?? null);
        if (null === $wishlist) {
            throw new NotFoundHttpException('Wishlist not found.');
        }

        /** @var ProductVariantInterface|null $variant */
        $variant = $this->productVariantRepository->findOneBy(['code' => $data->productVariantCode]);
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();
        if (null === $variant || !$this->availabilityChecker->canBeWishlisted($variant, $channel)) {
            throw new UnprocessableEntityHttpException(\sprintf('The product variant "%s" cannot be added to a wishlist.', $data->productVariantCode));
        }

        if ($wishlist->hasVariant($variant)) {
            throw new ConflictHttpException(\sprintf('The product variant "%s" is already in this wishlist.', $data->productVariantCode));
        }

        $item = $this->wishlistItemFactory->createForVariant($variant, $wishlist);
        $wishlist->addItem($item);
        $this->wishlistRepository->add($wishlist);

        return $item;
    }
}

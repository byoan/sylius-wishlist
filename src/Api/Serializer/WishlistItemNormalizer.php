<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Api\Serializer;

use Byoan\SyliusWishlistPlugin\Checker\WishlistAvailabilityCheckerInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Bundle\ApiBundle\SectionResolver\ShopApiSection;
use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/** Adds what a client needs to know about the product, including whether it can be bought. */
final class WishlistItemNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'byoan_sylius_wishlist_item_normalizer_already_called';

    public function __construct(
        private readonly WishlistAvailabilityCheckerInterface $availabilityChecker,
        private readonly SectionProviderInterface $sectionProvider,
    ) {
    }

    /** @return array<string, mixed> */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($data, WishlistItemInterface::class);

        $context[self::ALREADY_CALLED] = true;
        /** @var array<string, mixed> $normalized */
        $normalized = $this->normalizer->normalize($data, $format, $context);

        $variant = $data->getVariant();
        $channel = $data->getWishlist()?->getChannel();
        Assert::notNull($variant);
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $normalized['productVariantCode'] = $variant->getCode();
        $normalized['productVariantName'] = $variant->getName();
        $normalized['productCode'] = $variant->getProduct()?->getCode();
        $normalized['productName'] = $variant->getProduct()?->getName();
        $normalized['available'] = $this->availabilityChecker->isAvailable($variant, $channel);

        return $normalized;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof WishlistItemInterface &&
            !isset($context[self::ALREADY_CALLED]) &&
            $this->sectionProvider->getSection() instanceof ShopApiSection;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [WishlistItemInterface::class => false];
    }
}

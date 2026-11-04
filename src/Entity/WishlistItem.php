<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Entity;

use Sylius\Component\Core\Model\ProductVariantInterface;

class WishlistItem implements WishlistItemInterface
{
    protected ?int $id = null;

    protected ?WishlistInterface $wishlist = null;

    protected ?ProductVariantInterface $variant = null;

    protected ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWishlist(): ?WishlistInterface
    {
        return $this->wishlist;
    }

    public function setWishlist(?WishlistInterface $wishlist): void
    {
        $this->wishlist = $wishlist;
    }

    public function getVariant(): ?ProductVariantInterface
    {
        return $this->variant;
    }

    public function setVariant(?ProductVariantInterface $variant): void
    {
        $this->variant = $variant;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}

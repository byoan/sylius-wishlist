<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Resource\Model\ResourceInterface;
use Sylius\Resource\Model\TimestampableInterface;

interface WishlistInterface extends ResourceInterface, TimestampableInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function isDefault(): bool;

    public function setDefault(bool $default): void;

    /** Null for a guest wishlist, which is identified by its token instead. */
    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): void;

    /** Identifies the guest who owns the wishlist. Null once it belongs to a customer. */
    public function getToken(): ?string;

    public function setToken(?string $token): void;

    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;

    /** @return Collection<int, WishlistItemInterface> */
    public function getItems(): Collection;

    public function addItem(WishlistItemInterface $item): void;

    public function removeItem(WishlistItemInterface $item): void;

    public function getItemForVariant(ProductVariantInterface $variant): ?WishlistItemInterface;

    public function hasVariant(ProductVariantInterface $variant): bool;
}

<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Resource\Model\TimestampableTrait;

class Wishlist implements WishlistInterface
{
    use TimestampableTrait;

    protected ?int $id = null;

    protected ?string $name = null;

    /**
     * True for the default wishlist and null otherwise, never false. NULLs are distinct in the
     * (customer, channel, is_default) and (token, channel, is_default) unique indexes, so an owner
     * can have many wishlists per channel but only one default.
     */
    protected ?bool $default = null;

    protected ?CustomerInterface $customer = null;

    protected ?string $token = null;

    protected ?ChannelInterface $channel = null;

    /** @var Collection<int, WishlistItemInterface> */
    protected Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function isDefault(): bool
    {
        return true === $this->default;
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default ? true : null;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): void
    {
        $this->customer = $customer;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function setChannel(?ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(WishlistItemInterface $item): void
    {
        if ($this->items->contains($item)) {
            return;
        }

        $this->items->add($item);
        $item->setWishlist($this);
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function removeItem(WishlistItemInterface $item): void
    {
        if (!$this->items->removeElement($item)) {
            return;
        }

        $item->setWishlist(null);
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getItemForVariant(ProductVariantInterface $variant): ?WishlistItemInterface
    {
        foreach ($this->items as $item) {
            if ($item->getVariant()?->getCode() === $variant->getCode()) {
                return $item;
            }
        }

        return null;
    }

    public function hasVariant(ProductVariantInterface $variant): bool
    {
        return null !== $this->getItemForVariant($variant);
    }
}

<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Factory;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class DefaultWishlistFactory implements DefaultWishlistFactoryInterface
{
    /** @param FactoryInterface<WishlistInterface> $wishlistFactory */
    public function __construct(
        private FactoryInterface $wishlistFactory,
        private TranslatorInterface $translator,
    ) {
    }

    public function createForCustomer(ChannelInterface $channel, CustomerInterface $customer): WishlistInterface
    {
        $wishlist = $this->create($channel);
        $wishlist->setCustomer($customer);

        return $wishlist;
    }

    public function createForGuest(ChannelInterface $channel, ?string $token): WishlistInterface
    {
        $wishlist = $this->create($channel);
        $wishlist->setToken($token);

        return $wishlist;
    }

    private function create(ChannelInterface $channel): WishlistInterface
    {
        $wishlist = $this->wishlistFactory->createNew();
        $wishlist->setName($this->translator->trans('byoan_sylius_wishlist.ui.default_wishlist_name'));
        $wishlist->setDefault(true);
        $wishlist->setChannel($channel);

        return $wishlist;
    }
}

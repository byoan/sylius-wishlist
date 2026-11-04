<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Factory;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;

interface DefaultWishlistFactoryInterface
{
    public function createForCustomer(ChannelInterface $channel, CustomerInterface $customer): WishlistInterface;

    public function createForGuest(ChannelInterface $channel, ?string $token): WishlistInterface;
}

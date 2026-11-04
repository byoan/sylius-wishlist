<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Transfer;

use Sylius\Component\Customer\Model\CustomerInterface;

interface GuestWishlistTransferInterface
{
    /** Assigns the current guest's wishlists to the customer, in every channel. */
    public function transferTo(CustomerInterface $customer): void;
}

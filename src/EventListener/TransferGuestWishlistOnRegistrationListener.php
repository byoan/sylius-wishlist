<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\EventListener;

use Byoan\SyliusWishlistPlugin\Transfer\GuestWishlistTransferInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

/**
 * Listens to registration rather than login: a channel can require account verification, in which
 * case registering does not log the customer in and the verification link may open in another
 * browser, without this session.
 */
final readonly class TransferGuestWishlistOnRegistrationListener
{
    public function __construct(private GuestWishlistTransferInterface $transfer)
    {
    }

    public function __invoke(GenericEvent $event): void
    {
        $customer = $event->getSubject();
        Assert::isInstanceOf($customer, CustomerInterface::class);

        $this->transfer->transferTo($customer);
    }
}

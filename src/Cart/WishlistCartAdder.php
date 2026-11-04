<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Cart;

use Byoan\SyliusWishlistPlugin\Checker\WishlistAvailabilityCheckerInterface;
use Sylius\Bundle\OrderBundle\Adder\CartItemAdderInterface;
use Sylius\Bundle\OrderBundle\Factory\AddToCartCommandFactoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** Follows the path of the native add-to-cart form: the same command, validation and adder. */
final readonly class WishlistCartAdder implements WishlistCartAdderInterface
{
    /**
     * @param FactoryInterface<OrderItemInterface> $orderItemFactory
     * @param list<string> $validationGroups
     */
    public function __construct(
        private CartContextInterface $cartContext,
        private FactoryInterface $orderItemFactory,
        private OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private AddToCartCommandFactoryInterface $addToCartCommandFactory,
        private CartItemAdderInterface $cartItemAdder,
        private ValidatorInterface $validator,
        private WishlistAvailabilityCheckerInterface $availabilityChecker,
        private array $validationGroups,
    ) {
    }

    public function add(iterable $variants, ChannelInterface $channel): AddToCartResult
    {
        $cart = $this->cartContext->getCart();
        $added = [];
        $rejected = [];

        foreach ($variants as $variant) {
            if (!$this->availabilityChecker->isAvailable($variant, $channel)) {
                $rejected[] = $variant;

                continue;
            }

            /** @var OrderItemInterface $cartItem */
            $cartItem = $this->orderItemFactory->createNew();
            $cartItem->setVariant($variant);
            $this->orderItemQuantityModifier->modify($cartItem, 1);

            $command = $this->addToCartCommandFactory->createWithCartAndCartItem($cart, $cartItem);
            if (\count($this->validator->validate($command, null, $this->validationGroups)) > 0) {
                $rejected[] = $variant;

                continue;
            }

            $this->cartItemAdder->add($command);
            $added[] = $variant;
        }

        return new AddToCartResult($added, $rejected);
    }
}

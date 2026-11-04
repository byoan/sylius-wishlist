<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Form\Factory;

use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Form\FormInterface;

interface WishlistFormFactoryInterface
{
    /** @param array<string, mixed> $routeParameters */
    public function createItemForm(string $route, array $routeParameters = []): FormInterface;

    public function createWishlistForm(): FormInterface;

    public function createSetDefaultForm(WishlistInterface $wishlist): FormInterface;

    public function createMoveForm(WishlistItemInterface $item): FormInterface;

    public function createSelectionForm(WishlistInterface $wishlist, ChannelInterface $channel): FormInterface;
}

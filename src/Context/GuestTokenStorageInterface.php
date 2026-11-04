<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Context;

interface GuestTokenStorageInterface
{
    public function getToken(): ?string;

    public function getOrCreateToken(): string;

    public function clear(): void;
}

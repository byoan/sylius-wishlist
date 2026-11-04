<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Api\StateProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Same rule as the storefront: the default wishlist always has to exist.
 *
 * @implements ProcessorInterface<WishlistInterface, void>
 */
final readonly class DeleteWishlistProcessor implements ProcessorInterface
{
    /** @param ProcessorInterface<WishlistInterface, void> $removeProcessor */
    public function __construct(private ProcessorInterface $removeProcessor)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($data->isDefault()) {
            throw new ConflictHttpException('The default wishlist cannot be deleted.');
        }

        $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}

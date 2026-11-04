<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;

/** The target wishlist comes from the route, so this form only carries the CSRF token. */
final class WishlistSetDefaultType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
    }

    public function getBlockPrefix(): string
    {
        return 'byoan_sylius_wishlist_wishlist_set_default';
    }
}

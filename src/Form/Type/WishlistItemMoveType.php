<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Form\Type;

use Byoan\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotNull;

/** The choices are the shopper's other wishlists, so a product cannot be moved to anyone else's. */
final class WishlistItemMoveType extends AbstractResourceType
{
    /** @param string[] $validationGroups */
    public function __construct(
        string $dataClass,
        array $validationGroups,
        private readonly WishlistContextInterface $wishlistContext,
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $item = $event->getData();
            $current = $item instanceof WishlistItemInterface ? $item->getWishlist() : null;

            $event->getForm()->add('wishlist', ChoiceType::class, [
                'choices' => array_values(array_filter(
                    $this->wishlistContext->getWishlists(),
                    static fn (WishlistInterface $wishlist): bool => null !== $wishlist->getId() && $wishlist !== $current,
                )),
                'choice_value' => static fn (?WishlistInterface $wishlist): ?string => null !== $wishlist ? (string) $wishlist->getId() : null,
                'choice_label' => static fn (WishlistInterface $wishlist): string => (string) $wishlist->getName(),
                'choice_translation_domain' => false,
                'placeholder' => 'byoan_sylius_wishlist.ui.move_to',
                'label' => false,
                'constraints' => [new NotNull(groups: $this->validationGroups)],
            ]);
        });
    }

    public function getBlockPrefix(): string
    {
        return 'byoan_sylius_wishlist_wishlist_item_move';
    }
}

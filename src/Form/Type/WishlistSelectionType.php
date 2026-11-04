<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WishlistSelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('variants', ChoiceType::class, [
            'choices' => array_combine($options['variant_codes'], $options['variant_codes']),
            'choice_label' => false,
            'expanded' => true,
            'multiple' => true,
            'required' => false,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('variant_codes');
        $resolver->setAllowedTypes('variant_codes', 'string[]');
    }

    public function getBlockPrefix(): string
    {
        return 'byoan_sylius_wishlist_selection';
    }
}

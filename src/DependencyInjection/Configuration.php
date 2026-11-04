<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\DependencyInjection;

use Byoan\SyliusWishlistPlugin\Entity\Wishlist;
use Byoan\SyliusWishlistPlugin\Entity\WishlistInterface;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItem;
use Byoan\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Byoan\SyliusWishlistPlugin\Form\Type\WishlistItemType;
use Byoan\SyliusWishlistPlugin\Form\Type\WishlistType;
use Byoan\SyliusWishlistPlugin\Repository\WishlistRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Resource\Factory\Factory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('byoan_sylius_wishlist');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $resources = $rootNode
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
        ;

        $this->addResource($resources->arrayNode('wishlist'), Wishlist::class, WishlistInterface::class, WishlistRepository::class, WishlistType::class);
        $this->addResource($resources->arrayNode('wishlist_item'), WishlistItem::class, WishlistItemInterface::class, EntityRepository::class, WishlistItemType::class);

        return $treeBuilder;
    }

    private function addResource(ArrayNodeDefinition $node, string $model, string $interface, string $repository, string $form): void
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('options')->end()
                ->arrayNode('classes')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('model')->defaultValue($model)->cannotBeEmpty()->end()
                        ->scalarNode('interface')->defaultValue($interface)->cannotBeEmpty()->end()
                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                        ->scalarNode('repository')->defaultValue($repository)->cannotBeEmpty()->end()
                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                        ->scalarNode('form')->defaultValue($form)->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}

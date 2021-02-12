<?php


namespace Pimcore\Bundle\DataHubBatchImportBundle\DependencyInjection\CompilerPass;

use Pimcore\Bundle\DataHubBatchImportBundle\Resolver\ResolverFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


class ResolverConfigurationFactoryPass implements CompilerPassInterface
{
    CONST load_tag = 'pimcore.datahub.batch_import.resolver.load';
    CONST location_tag = 'pimcore.datahub.batch_import.resolver.location';
    CONST publish_tag = 'pimcore.datahub.batch_import.resolver.publish';
    CONST factory_tag = 'pimcore.datahub.batch_import.resolver.factory';


    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds(self::load_tag);
        $loadStrategies = [];
        if (sizeof($taggedServices)) {
            foreach ($taggedServices as $id => $tags) {
                foreach ($tags as $attributes) {
                    $loadStrategies[$attributes['type']] = new Reference($id);
                }
            }
        }

        $taggedServices = $container->findTaggedServiceIds(self::location_tag);
        $locationStrategies = [];
        if (sizeof($taggedServices)) {
            foreach ($taggedServices as $id => $tags) {
                foreach ($tags as $attributes) {
                    $locationStrategies[$attributes['type']] = new Reference($id);
                }
            }
        }

        $taggedServices = $container->findTaggedServiceIds(self::publish_tag);
        $publishStrategies = [];
        if (sizeof($taggedServices)) {
            foreach ($taggedServices as $id => $tags) {
                foreach ($tags as $attributes) {
                    $publishStrategies[$attributes['type']] = new Reference($id);
                }
            }
        }

        $taggedServices = $container->findTaggedServiceIds(self::factory_tag);
        $factories = [];
        if (sizeof($taggedServices)) {
            foreach ($taggedServices as $id => $tags) {
                foreach ($tags as $attributes) {
                    $factories[$attributes['type']] = new Reference($id);
                }
            }
        }

        $serviceLocator = $container->getDefinition(ResolverFactory::class);
        $serviceLocator->setArgument('$loadingStrategyBlueprints', $loadStrategies);
        $serviceLocator->setArgument('$locationStrategyBlueprints', $locationStrategies);
        $serviceLocator->setArgument('$publishingStrategyBlueprints', $publishStrategies);
        $serviceLocator->setArgument('$factoryBlueprints', $factories);
    }
}
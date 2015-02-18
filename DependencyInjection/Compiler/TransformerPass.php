<?php

namespace marmelab\NgAdminGeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class TransformerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $transformerServiceIds = $container->findTaggedServiceIds('ng_admin_generator.transformer');

        $configGenerator = $container->getDefinition('marmelab.ng_admin_generator.configuration_generator');
        foreach ($transformerServiceIds as $serviceId => $attributes) {
            $configGenerator->addMethodCall('addTransformer', [new Reference($serviceId)]);
        }

        return;
    }
}

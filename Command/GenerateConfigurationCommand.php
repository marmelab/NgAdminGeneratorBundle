<?php

namespace marmelab\NgAdminGeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateConfigurationCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this
            ->setName('ng-admin:configuration:generate')
            ->setDescription('Generate a ng-admin valid configuration based on configured REST APgI');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $container = $this->getContainer();
        $configurationGenerator = $container->get('marmelab.ng_admin_generator.configuration_generator');
        $entityConfigurationRetriever = $container->get('marmelab.ng_admin_generator.entity_configuration_retriever');

        $entities = [];
        $restRegistry = $container->get('lemon_rest.object_registry');
        foreach ($restRegistry->getClasses() as $entityClassName) {
            $classNameParts = explode('\\', $entityClassName);
            $varName = lcfirst(end($classNameParts));

            $entities[$varName] = $entityConfigurationRetriever->retrieveEntityConfiguration($entityClassName);
        }

        $output->writeln($configurationGenerator->generateConfiguration($entities));
    }
}

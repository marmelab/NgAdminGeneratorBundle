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
            ->setDescription('Generate a ng-admin valid configuration based on configured REST API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $container = $this->getContainer();
        $entityConfigurationRetriever = $container->get('marmelab.ng_admin_generator.entity_configuration_retriever');

        $restRegistry = $container->get('lemon_rest.object_registry');
        foreach ($restRegistry->getClasses() as $entityClassName) {
            $this->debug(sprintf('Entity found: <info>%s</info>', $entityClassName));

            $configuration = $entityConfigurationRetriever->retrieveEntityConfiguration($entityClassName);
            $this->showDebugConfiguration($configuration);
        }
    }

    private function debug($message)
    {
        if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERY_VERBOSE) {
            return;
        }

        $this->output->writeln($message);
    }

    private function showDebugConfiguration($configuration)
    {
        if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERY_VERBOSE) {
            return;
        }

        foreach ($configuration as $field) {
            $this->output->writeln(sprintf('  * Field <info>%s</info>: type => %s', $field['name'], $field['type']));
        }
    }
}

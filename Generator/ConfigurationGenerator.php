<?php

namespace marmelab\NgAdminGeneratorBundle\Generator;

use marmelab\NgAdminGeneratorBundle\Retriever\EntityConfigurationRetriever;

class ConfigurationGenerator
{
    private $retriever;
    private $twig;

    public function __construct(EntityConfigurationRetriever $retriever, \Twig_Environment $twig)
    {
        $this->retriever = $retriever;
        $this->twig = $twig;
    }

    public function generateConfiguration(array $classNames)
    {
        $entities = [];
        foreach ($classNames as $className) {
            $classNameParts = explode('\\', $className);
            $varName = lcfirst(end($classNameParts));
            $entities[$varName] = $this->retriever->retrieveEntityConfiguration($className);
        }

        return $this->twig->render('marmelabNgAdminGeneratorBundle:Configuration:config.js.twig', compact('entities'));
    }
}

<?php

namespace marmelab\NgAdminGeneratorBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;
use marmelab\NgAdminGeneratorBundle\Transformer\EntityReferencedFieldNameToMeaningfulNameTransformer;
use marmelab\NgAdminGeneratorBundle\Transformer\DoctrineToNgAdminTransformer;
use Symfony\Component\Form\DataTransformerInterface;

class ConfigurationGenerator
{
    private $em;
    private $twig;
    private $transformers = [];

    public function __construct(EntityManagerInterface $em, \Twig_Environment $twig)
    {
        $this->em = $em;
        $this->twig = $twig;
    }

    public function addTransformer(DataTransformerInterface $transformer)
    {
        $this->transformers[] = $transformer;
    }

    public function generateConfiguration(array $classNames)
    {
        $entities = $this->getClassesMetadata($classNames);
        foreach ($this->transformers as $transformer) {
            foreach ($entities as &$entity) {
                $entity = $transformer->transform($entity);
            }
        }

        return $this->twig->render('marmelabNgAdminGeneratorBundle:Configuration:config.js.twig', compact('entities'));
    }

    private function getClassesMetadata(array $classNames)
    {
        $entities = [];
        foreach ($classNames as $className) {
            $classNameParts = explode('\\', $className);
            $varName = lcfirst(end($classNameParts));

            $entities[$varName] = $this->em->getClassMetadata($className);
        }

        return $entities;
    }
}

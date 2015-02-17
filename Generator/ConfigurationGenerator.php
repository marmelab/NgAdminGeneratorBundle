<?php

namespace marmelab\NgAdminGeneratorBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;
use marmelab\NgAdminGeneratorBundle\Transformer\EntityReferencedFieldNameToMeaningfulNameTransformer;
use marmelab\NgAdminGeneratorBundle\Transformer\DoctrineToNgAdminTransformer;

class ConfigurationGenerator
{
    private $em;
    private $twig;

    public function __construct(EntityManagerInterface $em, \Twig_Environment $twig)
    {
        $this->em = $em;
        $this->twig = $twig;
    }

    public function generateConfiguration(array $classNames)
    {
        $transformer = new DoctrineToNgAdminTransformer();

        $entities = [];
        foreach ($classNames as $className) {
            $classNameParts = explode('\\', $className);
            $varName = lcfirst(end($classNameParts));

            $entities[$varName] = $transformer->transform($this->em->getClassMetadata($className));
        }

        $transformer = new EntityReferencedFieldNameToMeaningfulNameTransformer($this->em->getMetadataFactory());
        foreach ($entities as &$entity) {
            $entity = $transformer->transform($entity);
        }

        return $this->twig->render('marmelabNgAdminGeneratorBundle:Configuration:config.js.twig', compact('entities'));
    }
}

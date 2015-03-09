<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Inflector\Inflector;

class EntityToEntityWithIconTransformer implements TransformerInterface
{
    const DEFAULT_ICON = 'cog';

    private $iconMapping;

    public function __construct(array $iconMapping = [])
    {
        $this->iconMapping = $iconMapping;
    }

    public function transform($entity)
    {
        $entity['icon'] = $this->getIcon(
            Inflector::singularize($entity['name'])
        );

        return $entity;
    }

    public function reverseTransform($entity)
    {
        unset($entity['icon']);

        return $entity;
    }

    private function getIcon($entityName)
    {
        foreach ($this->iconMapping as $iconName => $acceptedEntityNames) {
            if (in_array($entityName, $acceptedEntityNames)) {
                return $iconName;
            }
        }

        return self::DEFAULT_ICON;
    }
}

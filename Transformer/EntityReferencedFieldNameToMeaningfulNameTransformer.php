<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use Doctrine\ORM\Mapping\ClassMetadataFactory;

class EntityReferencedFieldNameToMeaningfulNameTransformer
{
    private static $bestChoices = [
        'name',
        'title',
        'slug',
        'id',
    ];

    private $metadataFactory;

    public function __construct(ClassMetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function transform($entity)
    {
        foreach ($entity as $key => &$field) {
            if (!in_array($field['type'], ['reference', 'reference_many'])) {
                continue;
            }

            $entityFields = $this->metadataFactory->getMetadataFor($field['referencedEntity']['class']);
            $bestFields = array_intersect(self::$bestChoices, $entityFields->getFieldNames());
            if (!count($bestFields)) {
                continue;
            }

            $field['referencedField'] = current($bestFields);
        }

        return $entity;
    }

    public function reverseTransform()
    {
        throw new \DomainException("You shouldn't need to transform a ng-admin name into a Doctrine column name.");
    }
}

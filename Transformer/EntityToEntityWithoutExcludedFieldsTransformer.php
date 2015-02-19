<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use JMS\Serializer\Serializer;

class EntityToEntityWithoutExcludedFieldsTransformer implements TransformerInterface
{
    private $metadataFactory;

    public function __construct(Serializer $serializer)
    {
        $this->metadataFactory = $serializer->getMetadataFactory();
    }

    public function transform($entity)
    {
        $entityClass = current($entity)['class'];
        $fields = array_keys($this->metadataFactory->getMetadataForClass($entityClass)->propertyMetadata);

        $fieldsToRemove = array_diff(array_keys($entity), $fields);
        foreach ($fieldsToRemove as $fieldToRemove) {
            unset($entity[$fieldToRemove]);
        }

        return $entity;
    }

    public function reverseTransform($data)
    {
        throw new \DomainException("You shouldn't need to re-include excluded fields into your entities.");
    }
}

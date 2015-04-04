<?php

namespace marmelab\NgAdminGeneratorBundle\Guesser;

use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Serializer;

class ReferencedFieldGuesser
{
    private $metadataFactory;
    private $bestReferencedFieldChoices;

    public function __construct(Serializer $serializer, array $bestReferencedFieldChoices)
    {
        $this->metadataFactory = $serializer->getMetadataFactory();
        $this->bestReferencedFieldChoices = $bestReferencedFieldChoices;
    }

    public function guess($referencedClass)
    {
        $metadata = $this->metadataFactory->getMetadataForClass($referencedClass);

        $fieldNames = array_keys($metadata->propertyMetadata);
        $bestFields = array_intersect($this->bestReferencedFieldChoices, $fieldNames);

        // if we can't guess any field, return the first one (the id generally)
        if (!count($bestFields)) {
            return $fieldNames[0];
        }

        return current($bestFields);
    }

    public function guessTargetReferenceField($referencedClass)
    {
        // @TODO: find a more reliable way to get relationship column name
        $className = explode('\\', $referencedClass);
        $className = end($className);

        return $className ? strtolower($className).'_id' : null;
    }
}

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
            $property = reset($metadata->propertyMetadata);
            return $property->name;
        }

        return current($bestFields);
    }
}

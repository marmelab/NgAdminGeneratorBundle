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
        if (!count($bestFields)) {
            return null;
        }

        return current($bestFields);
    }
}

<?php

namespace marmelab\NgAdminGeneratorBundle\Retriever;

use Doctrine\ORM\EntityManagerInterface;

class EntityConfigurationRetriever
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function retrieveEntityConfiguration($className)
    {
        $fields = [];
        $metaData = $this->em->getClassMetadata($className);
        foreach ($metaData->fieldMappings as $fieldMapping) {
            $field = [
                'name' => $fieldMapping['fieldName'],
                'type' => $fieldMapping['type']
            ];

            $fields[] = $field;
        }

        return $fields;
    }
}

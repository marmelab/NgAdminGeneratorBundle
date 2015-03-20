<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadata;
use marmelab\NgAdminGeneratorBundle\Guesser\ReferencedFieldGuesser;

class NgAdminWithRelationshipsTransformer implements TransformerInterface
{
    private $metadataFactory;
    private $referencedFieldGuesser;

    public function __construct(EntityManagerInterface $entityManager, ReferencedFieldGuesser $referencedFieldGuesser)
    {
        $this->metadataFactory = $entityManager->getMetadataFactory();
        $this->referencedFieldGuesser = $referencedFieldGuesser;
    }

    public function transform($configuration)
    {
        $transformedConfiguration = $this->addForeignKeyFieldToReferencedFields($configuration);
        $transformedConfiguration = $this->transformReferenceRelationships($transformedConfiguration);

        return $transformedConfiguration;
    }

    public function reverseTransform($configWithRelationships)
    {
        throw new \DomainException("You shouldn't have to remove relationships from a ng-admin configuration.");
    }

    private function addForeignKeyFieldToReferencedFields($configuration)
    {
        $referenceFields = array_filter($configuration['fields'], function($field) {
            return in_array($field['type'], ['referenced_list', 'reference_many']);
        });

        if (!count($referenceFields)) {
            return $configuration;
        }

        $transformedConfiguration = $configuration;
        foreach ($referenceFields as $index => $referenceField) {
            // if referenced field already found with JMS serializer, just skip it.
            if ($referenceField['referencedField']) {
                continue;
            }

            if ($referenceField['type'] === 'referenced_list') {
                $targetEntity = $configuration['class'];
                $sourceEntity = $referenceField['referencedEntity']['class'];
            }

            $referenceMetadata = $this->metadataFactory->getMetadataFor($referenceField['referencedEntity']['class']);
            foreach ($referenceMetadata->associationMappings as $mapping) {
                if ($mapping['sourceEntity'] !== $sourceEntity && $mapping['targetEntity'] !== $targetEntity) {
                    continue;
                }

                if ($referenceField['type'] === 'referenced_list') {
                    $transformedConfiguration['fields'][$index]['referencedField'] = $mapping['targetToSourceKeyColumns']['id'];
                }
            }
        }

        return $transformedConfiguration;
    }

    /**
     * Turns foreign key columns into Reference field instead of simple "number" one.
     */
    private function transformReferenceRelationships($configuration)
    {
        $associationMappings = $this->metadataFactory->getMetadataFor($configuration['class'])->getAssociationMappings();
        if (!count($associationMappings)) {
            return $configuration;
        }

        $transformedConfiguration = $configuration;
        foreach ($associationMappings as $fieldName => $associationMapping) {
            // Try to find field to modify
            $fieldIndex = $this->getFieldIndex($configuration['fields'], $fieldName);
            if (!$fieldIndex) {
                // if not found, try with referenced column
                $fieldName = $associationMapping['joinColumns'][0]['name'];
                $fieldIndex = $this->getFieldIndex($configuration['fields'], $fieldName);
                if (!$fieldIndex) {
                    continue;
                }
            }

            // if field exists, convert it to a more friendly format
            switch ($associationMapping['type']) {
                case ClassMetadata::ONE_TO_ONE:
                    $transformedField = $this->transformOneToOneMapping($associationMapping);
                    break;

                case ClassMetadata::ONE_TO_MANY:
                    $transformedField = $this->transformOneToManyMapping($associationMapping);
                    break;

                case ClassMetadata::MANY_TO_ONE:
                    $transformedField = $this->transformManyToOneMapping($associationMapping);
                    break;

                case ClassMetadata::MANY_TO_MANY:
                    $transformedField = $this->transformManyToManyMapping($associationMapping);
                    break;

                default:
                    throw new \Exception('Unhandled relationship type: '.$associationMapping['type']);
            }

            $transformedConfiguration['fields'][$fieldIndex] =  $transformedField;
        }

        return $transformedConfiguration;
    }

    private function getFieldIndex(array $fields, $fieldName)
    {
        foreach($fields as $index => $field) {
            if ($field['name'] === $fieldName) {
                return $index;
            }
        }
    }

    private function transformOneToOneMapping($associationMapping)
    {
        return [
            'name' => $associationMapping['fieldName'],
            'type' => 'reference',
            'referencedEntity' => [
                'name' => $associationMapping['fieldName'],
                'class' => $associationMapping['targetEntity']
            ],
            'referencedField' => $this->referencedFieldGuesser->guess($associationMapping['targetEntity'])
        ];
    }

    private function transformOneToManyMapping($associationMapping)
    {
        return [
            'name' => $associationMapping['fieldName'],
            'type' => 'referenced_list',
            'referencedEntity' => [
                'name' => Inflector::pluralize($associationMapping['fieldName']),
                'class' => $associationMapping['targetEntity']
            ],
            'referencedField' => $this->referencedFieldGuesser->guess($associationMapping['targetEntity'])
        ];
    }

    private function transformManyToOneMapping($associationMapping)
    {
        return [
            'name' => $associationMapping['fieldName'],
            'type' => 'reference',
            'referencedEntity' => [
                'name' => Inflector::pluralize($associationMapping['fieldName']),
                'class' => $associationMapping['targetEntity']
            ],
            'referencedField' => $this->referencedFieldGuesser->guess($associationMapping['targetEntity'])
        ];
    }

    private function transformManyToManyMapping($associationMapping)
    {
        return [
            'name' => $associationMapping['fieldName'],
            'type' => 'reference_many',
            'referencedEntity' => [
                'name' => Inflector::pluralize($associationMapping['fieldName']),
                'class' => $associationMapping['targetEntity']
            ],
            'referencedField' => $this->referencedFieldGuesser->guess($associationMapping['targetEntity'])
        ];
    }
}
